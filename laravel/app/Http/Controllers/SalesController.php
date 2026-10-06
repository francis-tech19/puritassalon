<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\LoyaltyReward;
use App\Models\LoyaltySetting;
use App\Models\Notification;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    public function index(): View
    {
        $sales = Sale::with(['customer', 'employee', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $customers = Customer::where('status', 'ACTIVE')->orderBy('full_name')->get();
        $employees = Employee::where('status', 'ACTIVE')->orderBy('full_name')->get();
        $services = Service::where('status', 'ACTIVE')->orderBy('service_name')->get();
        $products = Inventory::where('status', '!=', 'OUT_OF_STOCK')->where('quantity', '>', 0)->orderBy('item_name')->get();

        return view('sales.index', compact('sales', 'customers', 'employees', 'services', 'products'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'employee_id' => 'nullable|exists:employees,id',
            'payment_method' => 'required|in:CASH,GCASH,MAYA,CARD,OTHER',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:SERVICE,PRODUCT',
            'items.*.item_id' => 'nullable|integer',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $rawTotal = 0;
            foreach ($validated['items'] as $item) {
                $rawTotal += $item['quantity'] * $item['unit_price'];
            }

            $discountPercentage = $validated['discount_percentage'] ?? 0;
            $discount = round($rawTotal * ($discountPercentage / 100), 2);
            $finalAmount = max(0, $rawTotal - $discount);

            $invoiceCode = 'INV-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));

            $sale = Sale::create([
                'invoice_code' => $invoiceCode,
                'customer_id' => $validated['customer_id'] ?? null,
                'employee_id' => $validated['employee_id'] ?? null,
                'payment_method' => $validated['payment_method'],
                'total_amount' => $rawTotal,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discount,
                'final_amount' => $finalAmount,
                'status' => 'COMPLETED',
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);

                // Deduct inventory if item is PRODUCT
                if ($item['item_type'] === 'PRODUCT') {
                    $inv = Inventory::whereKey($item['item_id'] ?? 0)->lockForUpdate()->first();
                    if (! $inv || $inv->quantity < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => 'The requested product quantity is no longer in stock. Please refresh the sale and try again.',
                        ]);
                    }

                    $previousStock = $inv->quantity;
                    $newQty = $previousStock - $item['quantity'];
                    $oldAlertLevel = Inventory::alertLevelForQuantity($previousStock);
                    $newStatus = Inventory::statusForQuantity($newQty);
                    $inv->update(['quantity' => $newQty, 'status' => $newStatus]);
                    InventoryTransaction::create([
                        'inventory_id' => $inv->id,
                        'transaction_type' => 'STOCK_OUT',
                        'action' => 'SALE',
                        'quantity_change' => -$item['quantity'],
                        'previous_stock' => $previousStock,
                        'new_stock' => $newQty,
                        'notes' => "Sale {$invoiceCode}",
                        'recorded_by' => Auth::id(),
                    ]);

                    $newAlertLevel = Inventory::alertLevelForQuantity($newQty);
                    if (in_array($newAlertLevel, ['LOW_STOCK', 'CRITICAL_STOCK', 'OUT_OF_STOCK'], true)
                        && $newAlertLevel !== $oldAlertLevel) {
                        $alertTitle = match ($newAlertLevel) {
                            'CRITICAL_STOCK' => 'Critical Inventory Alert',
                            'OUT_OF_STOCK' => 'Inventory Empty Alert',
                            default => 'Inventory Restock Alert',
                        };
                        Notification::create([
                            'title' => $alertTitle,
                            'message' => "Item '{$inv->item_name}' is {$newAlertLevel} ({$newQty} {$inv->unit} left).",
                            'type' => 'INVENTORY',
                        ]);
                    }
                }
            }

            // Update customer visit and spend
            if (! empty($validated['customer_id'])) {
                $customer = Customer::find($validated['customer_id']);
                if ($customer) {
                    $customer->increment('visit_count');
                    $customer->increment('total_spent', $finalAmount);

                    // Check loyalty milestone
                    $loyaltySetting = LoyaltySetting::where('is_active', true)->first();
                    if ($loyaltySetting && $loyaltySetting->visits_required_for_reward > 0) {
                        if ($customer->visit_count % $loyaltySetting->visits_required_for_reward === 0) {
                            LoyaltyReward::create([
                                'customer_id' => $customer->id,
                                'reward_title' => $loyaltySetting->reward_description,
                                'discount_percentage' => $loyaltySetting->discount_percentage,
                                'status' => 'AVAILABLE',
                                'issued_date' => now(),
                            ]);

                            Notification::create([
                                'title' => 'Loyalty Reward Milestone',
                                'message' => "Customer {$customer->full_name} reached {$customer->visit_count} visits and unlocked a reward!",
                                'type' => 'LOYALTY',
                            ]);
                        }
                    }
                }
            }

            AuditLog::log(Auth::id(), 'CHECKOUT', 'SALES', "Recorded sale {$invoiceCode} totaling ₱".number_format($finalAmount, 2));

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sale completed successfully!',
                    'sale' => $sale->load(['items', 'customer', 'employee']),
                ]);
            }

            return redirect()->route('sales.receipt', $sale->id)->with('success', 'Sale recorded successfully!');
        });
    }

    public function receipt(Sale $sale): View
    {
        $sale->load(['customer', 'employee', 'items']);
        $settings = BusinessSetting::first();

        return view('sales.receipt', compact('sale', 'settings'));
    }
}
