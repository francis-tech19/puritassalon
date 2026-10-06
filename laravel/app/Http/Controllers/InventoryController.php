<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $showLogs = $request->routeIs('inventory.logs');
        $query = Inventory::with(['transactions.user']);

        if ($status === 'LOW_STOCK') {
            $query->whereColumn('quantity', '<=', 'min_stock_level')->where('quantity', '>', 0);
        } elseif ($status === 'OUT_OF_STOCK') {
            $query->where('quantity', 0);
        }

        $items = $query->orderBy('item_name')->get();
        $lowStockCount = Inventory::whereColumn('quantity', '<=', 'min_stock_level')->count();
        $outOfStockCount = Inventory::where('quantity', 0)->count();
        $totalProducts = Inventory::count();
        $stockInToday = InventoryTransaction::whereDate('created_at', today())
            ->where(function ($query): void {
                $query->whereIn('action', ['STOCK_IN', 'RESTOCK', 'RETURNED'])
                    ->orWhere(fn ($legacyQuery) => $legacyQuery->whereNull('action')->where('transaction_type', 'STOCK_IN'));
            })
            ->sum(DB::raw('ABS(quantity_change)'));
        $stockOutToday = InventoryTransaction::whereDate('created_at', today())
            ->where(function ($query): void {
                $query->whereIn('action', ['STOCK_OUT', 'SALE', 'DAMAGED', 'EXPIRED', 'TRANSFER'])
                    ->orWhere(fn ($legacyQuery) => $legacyQuery->whereNull('action')->where('transaction_type', 'STOCK_OUT'));
            })
            ->sum(DB::raw('ABS(quantity_change)'));
        $transactionsQuery = InventoryTransaction::with(['inventory', 'user'])->latest();
        $recentTransactions = $showLogs ? $transactionsQuery->paginate(50) : $transactionsQuery->take(10)->get();

        return view('inventory.index', compact(
            'items',
            'status',
            'lowStockCount',
            'outOfStockCount',
            'totalProducts',
            'stockInToday',
            'stockOutToday',
            'recentTransactions',
            'showLogs'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:20',
            'min_stock_level' => 'required|integer|min:1',
            'supplier' => 'nullable|string|max:100',
        ]);

        $code = 'INV-'.strtoupper(substr(uniqid(), -4));
        $status = Inventory::statusForQuantity($validated['quantity']);

        $item = DB::transaction(function () use ($validated, $code, $status): Inventory {
            $item = Inventory::create(array_merge($validated, [
                'item_code' => $code,
                'status' => $status,
            ]));

            if ($validated['quantity'] > 0) {
                InventoryTransaction::create([
                    'inventory_id' => $item->id,
                    'transaction_type' => 'STOCK_IN',
                    'action' => 'STOCK_IN',
                    'quantity_change' => $validated['quantity'],
                    'previous_stock' => 0,
                    'new_stock' => $validated['quantity'],
                    'notes' => 'Initial stock on item creation',
                    'recorded_by' => Auth::id(),
                ]);
            }

            return $item;
        });

        AuditLog::log(Auth::id(), 'CREATE', 'INVENTORY', "Added inventory item {$item->item_name} ({$code})");

        return back()->with('success', "Item {$item->item_name} added to inventory!");
    }

    public function adjustStock(Request $request, Inventory $inventory): RedirectResponse
    {
        $validated = $request->validate([
            'transaction_type' => 'required|in:STOCK_IN,STOCK_OUT,SALE,DAMAGED,EXPIRED,RETURNED,ADJUSTMENT,TRANSFER,RESTOCK',
            'quantity_change' => 'required|integer|between:-100000,100000|not_in:0',
            'notes' => 'required|string|max:1000',
        ]);

        $result = DB::transaction(function () use ($inventory, $validated): array {
            $lockedInventory = Inventory::whereKey($inventory->id)->lockForUpdate()->firstOrFail();
            $action = $validated['transaction_type'];
            $amount = abs($validated['quantity_change']);
            $change = match ($action) {
                'STOCK_IN', 'RETURNED', 'RESTOCK' => $amount,
                'STOCK_OUT', 'SALE', 'DAMAGED', 'EXPIRED', 'TRANSFER' => -$amount,
                'ADJUSTMENT' => $validated['quantity_change'],
            };
            $previousStock = $lockedInventory->quantity;
            $newQty = $previousStock + $change;
            if ($newQty < 0) {
                return ['error' => 'This movement would make the product stock negative.'];
            }

            $oldAlertLevel = Inventory::alertLevelForQuantity($previousStock);
            $newAlertLevel = Inventory::alertLevelForQuantity($newQty);
            $lockedInventory->update([
                'quantity' => $newQty,
                'status' => Inventory::statusForQuantity($newQty),
            ]);

            $transactionType = match ($action) {
                'STOCK_IN', 'RETURNED', 'RESTOCK' => 'STOCK_IN',
                'STOCK_OUT', 'SALE', 'DAMAGED', 'EXPIRED', 'TRANSFER' => 'STOCK_OUT',
                default => 'ADJUSTMENT',
            };
            InventoryTransaction::create([
                'inventory_id' => $lockedInventory->id,
                'transaction_type' => $transactionType,
                'action' => $action,
                'quantity_change' => $change,
                'previous_stock' => $previousStock,
                'new_stock' => $newQty,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            if (in_array($newAlertLevel, ['LOW_STOCK', 'CRITICAL_STOCK', 'OUT_OF_STOCK'], true)
                && $newAlertLevel !== $oldAlertLevel) {
                $alertTitle = match ($newAlertLevel) {
                    'CRITICAL_STOCK' => 'Critical Inventory Alert',
                    'OUT_OF_STOCK' => 'Inventory Empty Alert',
                    default => 'Inventory Restock Alert',
                };
                Notification::create([
                    'title' => $alertTitle,
                    'message' => "Item '{$lockedInventory->item_name}' is {$newAlertLevel} ({$newQty} {$lockedInventory->unit} left).",
                    'type' => 'INVENTORY',
                ]);
            }

            return ['inventory' => $lockedInventory, 'change' => $change];
        });

        if (isset($result['error'])) {
            return back()->withErrors(['quantity_change' => $result['error']])->withInput();
        }

        $inventory = $result['inventory'];
        $change = $result['change'];
        AuditLog::log(Auth::id(), 'ADJUST_STOCK', 'INVENTORY', "Adjusted stock for {$inventory->item_name} by {$change}. New qty: {$inventory->quantity}");

        return back()->with('success', "Stock updated for {$inventory->item_name}.");
    }

    public function update(Request $request, Inventory $inventory): RedirectResponse
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'unit' => 'required|string|max:20',
            'min_stock_level' => 'required|integer|min:1',
            'supplier' => 'nullable|string|max:100',
        ]);

        $inventory->update($validated);

        AuditLog::log(Auth::id(), 'UPDATE', 'INVENTORY', "Updated inventory item {$inventory->item_name}");

        return back()->with('success', 'Item details updated.');
    }

    public function destroy(Inventory $inventory): RedirectResponse
    {
        if ($inventory->transactions()->exists()) {
            return back()->withErrors(['inventory' => 'This product has audit history and cannot be deleted. Keep the product record to preserve its logs.']);
        }

        AuditLog::log(Auth::id(), 'DELETE', 'INVENTORY', "Deleted inventory item {$inventory->item_name}");
        $inventory->delete();

        return back()->with('success', 'Item removed from inventory.');
    }
}
