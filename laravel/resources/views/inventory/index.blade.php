@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addModal: false, adjustModal: false, selectedItem: null }">
    <div class="flex flex-col gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">{{ $showLogs ? 'Product Logs' : 'Salon Inventory & Stock' }}</h1>
            <p class="text-gray-600 font-semibold mt-0.5">{{ $showLogs ? 'Review the recorded history of stock movements and adjustments.' : 'Track salon supplies, hair coloring tubes, shampoos, and retail products.' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 lg:flex-nowrap">
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Inventory</a>
            <a href="{{ route('inventory.logs') }}" class="btn btn-secondary">Product Logs</a>
            @unless($showLogs)
                <button @click="addModal = true" class="btn btn-primary shadow-md"><i data-lucide="package-plus" class="w-5 h-5"></i> Add Inventory Item</button>
            @endunless
        </div>
    </div>

    <section class="grid grid-cols-2 lg:grid-cols-5 gap-3" aria-label="Inventory statistics">
        @foreach([
            ['Total Products', $totalProducts, 'text-gray-900'],
            ['Low Stock', $lowStockCount, 'text-amber-700'],
            ['Out of Stock', $outOfStockCount, 'text-red-700'],
            ['Stock In Today', $stockInToday, 'text-emerald-700'],
            ['Stock Out Today', $stockOutToday, 'text-rose-700'],
        ] as [$label, $value, $color])
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                <div class="text-xs font-bold uppercase text-gray-500">{{ $label }}</div>
                <div class="mt-1 text-2xl font-black {{ $color }}">{{ $value }}</div>
            </div>
        @endforeach
    </section>

    @unless($showLogs)
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            @if($lowStockCount > 0)
                <div class="p-3.5 rounded-2xl bg-amber-50 border-2 border-amber-400 text-amber-900 font-extrabold flex items-center gap-3 text-sm flex-1">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-600 shrink-0"></i>
                    <span>{{ $lowStockCount }} items are at or below minimum stock.</span>
                </div>
            @endif
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('inventory.index') }}" class="px-3 py-1.5 rounded-xl font-bold text-xs {{ empty($status) || $status === 'ALL' ? 'bg-[#7A1C49] text-white' : 'bg-white border text-gray-700' }}">All Items</a>
                <a href="{{ route('inventory.index', ['status' => 'LOW_STOCK']) }}" class="px-3 py-1.5 rounded-xl font-bold text-xs {{ $status === 'LOW_STOCK' ? 'bg-amber-600 text-white' : 'bg-white border text-amber-800' }}">Low Stock</a>
                <a href="{{ route('inventory.index', ['status' => 'OUT_OF_STOCK']) }}" class="px-3 py-1.5 rounded-xl font-bold text-xs {{ $status === 'OUT_OF_STOCK' ? 'bg-red-600 text-white' : 'bg-white border text-red-800' }}">Out of Stock</a>
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="inventory-table w-full text-left border-collapse">
                    <thead><tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b-2 border-gray-200">
                        <th class="py-3 px-4">Item Code & Name</th><th class="py-3 px-4">Category</th><th class="py-3 px-4 text-center">Stock Level</th><th class="py-3 px-4 text-center">Min Threshold</th><th class="py-3 px-4">Supplier</th><th class="py-3 px-4 text-center">Status</th><th class="py-3 px-4 text-right">Quick Stock</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200 text-sm font-semibold">
                        @forelse($items as $item)
                            <tr class="hover:bg-pink-50/50 transition">
                                <td class="py-4 px-4"><div class="font-extrabold text-gray-900">{{ $item->item_name }}</div><span class="text-xs font-bold text-gray-400 uppercase">{{ $item->item_code }}</span></td>
                                <td class="py-4 px-4 font-bold text-gray-700">{{ $item->category }}</td>
                                <td class="py-4 px-4 text-center font-black text-lg {{ $item->quantity <= $item->min_stock_level ? 'text-amber-700' : 'text-gray-900' }}">{{ $item->quantity }} <span class="text-xs font-semibold text-gray-500">{{ $item->unit }}</span></td>
                                <td class="py-4 px-4 text-center font-bold text-gray-600">{{ $item->min_stock_level }} {{ $item->unit }}</td>
                                <td class="py-4 px-4 text-xs font-bold text-gray-600">{{ $item->supplier ?? 'Direct Supplier' }}</td>
                                <td class="py-4 px-4 text-center">
                                    @php($stockLevel = \App\Models\Inventory::alertLevelForQuantity($item->quantity))
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black {{ $stockLevel === 'GOOD_STOCK' ? 'bg-emerald-100 text-emerald-800' : ($stockLevel === 'LOW_STOCK' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">{{ str_replace('_', ' ', $stockLevel) }}</span>
                                </td>
                                <td class="py-4 px-4 text-right"><button type="button" @click="selectedItem = {{ json_encode($item) }}; adjustModal = true" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-[#7A1C49] hover:text-white text-xs font-black transition border border-gray-300">± Adjust Stock</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-12 text-center text-gray-500">No inventory items match this filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endunless

    @if($showLogs)
        <section class="card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead><tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b-2 border-gray-200">
                        <th class="px-4 py-3">Date & Time</th><th class="px-4 py-3">Product / SKU</th><th class="px-4 py-3">Action</th><th class="px-4 py-3">Quantity</th><th class="px-4 py-3">Previous → New</th><th class="px-4 py-3">User</th><th class="px-4 py-3">Reason / Remarks</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-200 text-sm">
                        @forelse($recentTransactions as $transaction)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $transaction->created_at->format('M j, Y g:i A') }}</td>
                                <td class="px-4 py-3"><strong>{{ $transaction->inventory->item_name ?? 'Removed product' }}</strong><span class="block text-xs text-gray-500">{{ $transaction->inventory->item_code ?? 'SKU unavailable' }}</span></td>
                                <td class="px-4 py-3 font-bold">{{ $transaction->action ?? $transaction->transaction_type }}</td>
                                <td class="px-4 py-3 font-bold {{ $transaction->quantity_change < 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $transaction->quantity_change > 0 ? '+' : '' }}{{ $transaction->quantity_change }}</td>
                                <td class="px-4 py-3">{{ $transaction->previous_stock ?? '—' }} → {{ $transaction->new_stock ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $transaction->user->name ?? 'System / deleted user' }}</td>
                                <td class="px-4 py-3">{{ $transaction->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">No product activity has been recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <div class="border-t p-4">{{ $recentTransactions->links() }}</div>
    @else
        <section class="space-y-3">
            <div class="flex items-center justify-between"><h2 class="text-lg font-black text-gray-900">Recent Product Activity</h2><a href="{{ route('inventory.logs') }}" class="text-sm font-bold text-[#7A1C49]">View all logs</a></div>
            <div class="divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white">
                @forelse($recentTransactions->take(5) as $transaction)
                    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm"><div><strong class="text-gray-900">{{ $transaction->inventory->item_name ?? 'Removed product' }}</strong><span class="ml-2 text-gray-600">{{ $transaction->action ?? $transaction->transaction_type }} {{ $transaction->quantity_change > 0 ? '+' : '' }}{{ $transaction->quantity_change }}</span></div><span class="text-xs text-gray-500">{{ $transaction->user->name ?? 'System' }} · {{ $transaction->created_at->diffForHumans() }}</span></div>
                @empty
                    <p class="px-4 py-6 text-sm text-gray-500">No product activity yet.</p>
                @endforelse
            </div>
        </section>
    @endif

    @unless($showLogs)
        <div x-show="adjustModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" style="display: none;" role="dialog" aria-modal="true">
            <div @click="adjustModal = false" class="fixed inset-0 modal-backdrop"></div>
            <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-md border border-gray-200 p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 mb-4 border-b"><h3 class="text-xl font-extrabold text-[#7A1C49]">Adjust Stock</h3><button @click="adjustModal = false" class="text-gray-400 hover:text-gray-700"><i data-lucide="x" class="w-6 h-6"></i></button></div>
                <template x-if="selectedItem">
                    <form :action="'{{ url('/inventory') }}/' + selectedItem.id + '/adjust'" method="POST" class="space-y-4">
                        @csrf
                        <div><span class="text-xs font-bold uppercase text-gray-500">Item</span><div class="font-extrabold text-base text-gray-900" x-text="selectedItem.item_name"></div><div class="text-xs font-bold text-gray-500" x-text="'Current Stock: ' + selectedItem.quantity + ' ' + selectedItem.unit"></div></div>
                        <label class="block text-sm font-extrabold text-gray-900">Movement type
                            <select name="transaction_type" required class="form-select mt-1 font-bold">
                                <option value="STOCK_IN">Stock In</option><option value="STOCK_OUT">Stock Out</option><option value="SALE">Sale</option><option value="DAMAGED">Damaged</option><option value="EXPIRED">Expired</option><option value="RETURNED">Returned</option><option value="TRANSFER">Transfer</option><option value="RESTOCK">Restock</option><option value="ADJUSTMENT">Adjustment (+/-)</option>
                            </select>
                        </label>
                        <label class="block text-sm font-extrabold text-gray-900">Quantity
                            <input type="number" name="quantity_change" min="-100000" max="100000" value="1" required class="form-input mt-1 font-bold">
                        </label>
                        <label class="block text-sm font-extrabold text-gray-900">Reason / remarks
                            <input type="text" name="notes" placeholder="e.g. Damaged during handling" required maxlength="1000" class="form-input mt-1 font-bold">
                        </label>
                        <div class="flex justify-end gap-3 pt-4 border-t"><button type="button" @click="adjustModal = false" class="btn btn-secondary">Cancel</button><button type="submit" class="btn btn-primary">Update Stock</button></div>
                    </form>
                </template>
            </div>
        </div>

        <div x-show="addModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" style="display: none;" role="dialog" aria-modal="true">
            <div @click="addModal = false" class="fixed inset-0 modal-backdrop"></div>
            <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-lg border border-gray-200 p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 mb-4 border-b"><h3 class="text-xl font-extrabold text-[#7A1C49]">Add Inventory Item</h3><button @click="addModal = false" class="text-gray-400 hover:text-gray-700"><i data-lucide="x" class="w-6 h-6"></i></button></div>
                <form method="POST" action="{{ route('inventory.store') }}" class="space-y-4">
                    @csrf
                    <label class="block text-sm font-extrabold text-gray-900">Item Name<input type="text" name="item_name" required class="form-input mt-1 font-bold"></label>
                    <div class="grid grid-cols-2 gap-4"><label class="text-sm font-extrabold text-gray-900">Category<input type="text" name="category" required value="Hair Supplies" class="form-input mt-1 font-bold"></label><label class="text-sm font-extrabold text-gray-900">Unit<input type="text" name="unit" required value="pcs" class="form-input mt-1 font-bold"></label></div>
                    <div class="grid grid-cols-2 gap-4"><label class="text-sm font-extrabold text-gray-900">Initial Stock<input type="number" name="quantity" min="0" value="0" required class="form-input mt-1 font-bold"></label><label class="text-sm font-extrabold text-gray-900">Minimum Stock<input type="number" name="min_stock_level" min="1" value="4" required class="form-input mt-1 font-bold"></label></div>
                    <label class="block text-sm font-extrabold text-gray-900">Supplier<input type="text" name="supplier" class="form-input mt-1 font-bold"></label>
                    <div class="flex justify-end gap-3 pt-4 border-t"><button type="button" @click="addModal = false" class="btn btn-secondary">Cancel</button><button type="submit" class="btn btn-primary">Save Item</button></div>
                </form>
            </div>
        </div>
    @endunless
</div>
@endsection