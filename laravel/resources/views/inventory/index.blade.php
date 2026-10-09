@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    addModal: false, 
    adjustModal: false, 
    selectedItem: {{ $selectedProduct ? json_encode($selectedProduct) : 'null' }} 
}">
    <!-- Page Header & Main Navigation -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">
                    {{ $showLogs ? ($selectedProduct ? 'Logs: ' . $selectedProduct->item_name : 'Product Logs') : 'Salon Inventory & Stock' }}
                </h1>
                @if($showLogs && $selectedProduct)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-pink-100 text-[#7A1C49] border border-pink-200 uppercase tracking-wider">
                        {{ $selectedProduct->item_code }}
                    </span>
                @endif
            </div>
            <p class="text-gray-600 font-semibold mt-0.5">
                @if($showLogs && $selectedProduct)
                    Reviewing recorded stock movement logs for <strong class="text-gray-800">{{ $selectedProduct->item_name }}</strong>.
                @elseif($showLogs)
                    Review the recorded history of stock movements, sales, and manual adjustments across all products.
                @else
                    Track salon supplies, hair coloring tubes, shampoos, retail items, and manage replenishment.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex rounded-xl bg-gray-100 p-1 border border-gray-200">
                <a href="{{ route('inventory.index') }}" 
                   class="px-4 py-2 rounded-lg text-xs font-extrabold transition {{ !$showLogs ? 'bg-[#7A1C49] text-white shadow-sm' : 'text-gray-700 hover:text-[#7A1C49]' }}">
                    <i data-lucide="boxes" class="w-3.5 h-3.5 inline mr-1"></i> Inventory Items
                </a>
                <a href="{{ route('inventory.logs') }}" 
                   class="px-4 py-2 rounded-lg text-xs font-extrabold transition {{ $showLogs ? 'bg-[#7A1C49] text-white shadow-sm' : 'text-gray-700 hover:text-[#7A1C49]' }}">
                    <i data-lucide="history" class="w-3.5 h-3.5 inline mr-1"></i> Product Logs
                </a>
            </div>
            <button @click="addModal = true" class="btn btn-primary shadow-md">
                <i data-lucide="package-plus" class="w-4 h-4"></i> Add New Item
            </button>
        </div>
    </div>

    <!-- Statistics Section -->
    @if($showLogs && $selectedProduct && $productStats)
        <!-- Selected Product Focus Card -->
        <section class="rounded-2xl border-2 border-pink-200 bg-gradient-to-r from-pink-50/70 via-white to-pink-50/40 p-5 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-black uppercase tracking-wider text-gray-500">Selected Product Focus</span>
                        <span class="px-2 py-0.5 rounded text-xs font-black bg-gray-200 text-gray-800">{{ $selectedProduct->category }}</span>
                        <?php $stockLevel = \App\Models\Inventory::alertLevelForQuantity($selectedProduct->quantity); ?>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black {{ $stockLevel === 'GOOD_STOCK' ? 'bg-emerald-100 text-emerald-800' : ($stockLevel === 'LOW_STOCK' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                            {{ str_replace('_', ' ', $stockLevel) }}
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900">{{ $selectedProduct->item_name }}</div>
                    <div class="text-xs text-gray-600 font-semibold flex flex-wrap gap-4">
                        <span><strong class="text-gray-700">Code:</strong> {{ $selectedProduct->item_code }}</span>
                        <span><strong class="text-gray-700">Supplier:</strong> {{ $selectedProduct->supplier ?? 'Direct Supplier' }}</span>
                        <span><strong class="text-gray-700">Min Threshold:</strong> {{ $selectedProduct->min_stock_level }} {{ $selectedProduct->unit }}</span>
                    </div>
                </div>

                <!-- Product Mini Stats & Quick Actions -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-xl border border-pink-200 bg-white px-4 py-2.5 text-center min-w-[100px] shadow-sm">
                        <div class="text-[11px] font-bold uppercase text-gray-500">Current Stock</div>
                        <div class="text-xl font-black text-[#7A1C49]">{{ $selectedProduct->quantity }} <span class="text-xs font-semibold text-gray-500">{{ $selectedProduct->unit }}</span></div>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-center min-w-[100px] shadow-sm">
                        <div class="text-[11px] font-bold uppercase text-emerald-700">Total Stock In</div>
                        <div class="text-xl font-black text-emerald-700">+{{ $productStats['total_in'] }}</div>
                    </div>
                    <div class="rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-center min-w-[100px] shadow-sm">
                        <div class="text-[11px] font-bold uppercase text-rose-700">Total Stock Out</div>
                        <div class="text-xl font-black text-rose-700">-{{ $productStats['total_out'] }}</div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-center min-w-[90px] shadow-sm">
                        <div class="text-[11px] font-bold uppercase text-gray-500">Total Logs</div>
                        <div class="text-xl font-black text-gray-800">{{ $productStats['total_logs'] }}</div>
                    </div>
                    <div class="flex flex-col gap-1.5 ml-1">
                        <button type="button" @click="selectedItem = {{ json_encode($selectedProduct) }}; adjustModal = true" class="btn btn-primary text-xs py-2 px-3 shadow">
                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i> Adjust Stock
                        </button>
                        <a href="{{ route('inventory.logs') }}" class="btn btn-secondary text-xs py-1.5 px-3 text-center">
                            ✕ All Products
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @else
        <!-- Global Inventory Stats -->
        <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3" aria-label="Inventory statistics">
            @foreach($statsList as $stat)
                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm hover:border-pink-300 transition">
                    <div class="flex items-center justify-between text-xs font-bold uppercase text-gray-500">
                        <span>{{ $stat['label'] }}</span>
                        <i data-lucide="{{ $stat['icon'] }}" class="w-4 h-4 {{ $stat['color'] }} opacity-80"></i>
                    </div>
                    <div class="mt-1 text-2xl font-black {{ $stat['color'] }}">{{ $stat['value'] }}</div>
                </div>
            @endforeach
        </section>
    @endif

    <!-- MAIN TAB 1: INVENTORY ITEMS TABLE -->
    @if(!$showLogs)
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            @if($lowStockCount > 0)
                <div class="p-3.5 rounded-2xl bg-amber-50 border-2 border-amber-400 text-amber-900 font-extrabold flex items-center gap-3 text-sm flex-1">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-600 shrink-0"></i>
                    <span>{{ $lowStockCount }} items are at or below minimum stock threshold.</span>
                </div>
            @endif

            <!-- Search and Status Filter Form -->
            <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-gray-400"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search name, SKU, category..." class="form-input text-xs font-semibold pl-9 pr-3 py-1.5 w-60 rounded-xl">
                </div>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('inventory.index', array_merge(request()->except('status'), ['status' => 'ALL'])) }}" class="px-3 py-1.5 rounded-xl font-bold text-xs {{ empty($status) || $status === 'ALL' ? 'bg-[#7A1C49] text-white shadow-sm' : 'bg-white border text-gray-700 hover:bg-gray-50' }}">All</a>
                    <a href="{{ route('inventory.index', array_merge(request()->except('status'), ['status' => 'LOW_STOCK'])) }}" class="px-3 py-1.5 rounded-xl font-bold text-xs {{ $status === 'LOW_STOCK' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white border text-amber-800 hover:bg-amber-50' }}">Low Stock</a>
                    <a href="{{ route('inventory.index', array_merge(request()->except('status'), ['status' => 'OUT_OF_STOCK'])) }}" class="px-3 py-1.5 rounded-xl font-bold text-xs {{ $status === 'OUT_OF_STOCK' ? 'bg-red-600 text-white shadow-sm' : 'bg-white border text-red-800 hover:bg-red-50' }}">Out of Stock</a>
                    @if(!empty($search) || !empty($status))
                        <a href="{{ route('inventory.index') }}" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 text-xs font-bold" title="Reset Filters">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card p-0 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="inventory-table w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b-2 border-gray-200">
                            <th class="py-3 px-4">Item Code & Name</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4 text-center">Stock Level</th>
                            <th class="py-3 px-4 text-center">Min Threshold</th>
                            <th class="py-3 px-4">Supplier</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm font-semibold">
                        @forelse($items as $item)
                            <tr class="hover:bg-pink-50/40 transition">
                                <td class="py-4 px-4">
                                    <div class="font-extrabold text-gray-900">{{ $item->item_name }}</div>
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">{{ $item->item_code }}</span>
                                </td>
                                <td class="py-4 px-4 font-bold text-gray-700">{{ $item->category }}</td>
                                <td class="py-4 px-4 text-center font-black text-lg {{ $item->quantity <= $item->min_stock_level ? 'text-amber-700' : 'text-gray-900' }}">
                                    {{ $item->quantity }} <span class="text-xs font-semibold text-gray-500">{{ $item->unit }}</span>
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-gray-600">{{ $item->min_stock_level }} {{ $item->unit }}</td>
                                <td class="py-4 px-4 text-xs font-bold text-gray-600">{{ $item->supplier ?? 'Direct Supplier' }}</td>
                                <td class="py-4 px-4 text-center">
                                    <?php $stockLevel = \App\Models\Inventory::alertLevelForQuantity($item->quantity); ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black {{ $stockLevel === 'GOOD_STOCK' ? 'bg-emerald-100 text-emerald-800' : ($stockLevel === 'LOW_STOCK' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                        {{ str_replace('_', ' ', $stockLevel) }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <!-- View Product Logs button -->
                                        <a href="{{ route('inventory.logs', ['product_id' => $item->id]) }}" 
                                           class="px-2.5 py-1.5 rounded-lg bg-pink-50 hover:bg-[#7A1C49] text-[#7A1C49] hover:text-white text-xs font-black transition border border-pink-200 inline-flex items-center gap-1 shadow-sm"
                                           title="View stock movement logs for {{ $item->item_name }}">
                                            <i data-lucide="history" class="w-3.5 h-3.5"></i> Logs
                                        </a>
                                        <!-- Quick Adjust Stock button -->
                                        <button type="button" 
                                                @click="selectedItem = {{ json_encode($item) }}; adjustModal = true" 
                                                class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-[#7A1C49] hover:text-white text-gray-800 text-xs font-black transition border border-gray-300 inline-flex items-center gap-1 shadow-sm">
                                            <i data-lucide="plus-minus" class="w-3.5 h-3.5"></i> Adjust
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-500 font-bold">
                                    <i data-lucide="package-x" class="w-10 h-10 mx-auto mb-2 text-gray-300"></i>
                                    No inventory items match your current filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activity Mini Section -->
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black text-gray-900 flex items-center gap-2">
                    <i data-lucide="activity" class="w-5 h-5 text-[#7A1C49]"></i> Recent Stock Movements
                </h2>
                <a href="{{ route('inventory.logs') }}" class="text-sm font-bold text-[#7A1C49] hover:underline inline-flex items-center gap-1">
                    View all product logs <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
            <div class="divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                @forelse($recentTransactions->take(6) as $transaction)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('inventory.logs', ['product_id' => $transaction->inventory_id]) }}" 
                               class="font-extrabold text-[#7A1C49] hover:underline" 
                               title="Filter logs by this product">
                                {{ $transaction->inventory->item_name ?? 'Removed product' }}
                            </a>
                            <span class="px-2 py-0.5 rounded text-xs font-bold {{ $transaction->quantity_change > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $transaction->action ?? $transaction->transaction_type }} {{ $transaction->quantity_change > 0 ? '+' : '' }}{{ $transaction->quantity_change }}
                            </span>
                            @if($transaction->notes)
                                <span class="text-xs text-gray-500 italic truncate max-w-xs">({{ $transaction->notes }})</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 font-semibold">
                            <span>{{ $transaction->user->name ?? 'System' }}</span>
                            <span>•</span>
                            <span>{{ $transaction->created_at->diffForHumans() }}</span>
                            <a href="{{ route('inventory.logs', ['product_id' => $transaction->inventory_id]) }}" class="text-[#7A1C49] hover:underline font-bold ml-1">
                                Product Logs &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-6 text-sm text-gray-500 text-center font-bold">No product activity recorded yet.</p>
                @endforelse
            </div>
        </section>
    @else
        <!-- MAIN TAB 2: PRODUCT LOGS VIEW -->
        <!-- Product Logs Filter Bar -->
        <section class="card p-4 bg-white border border-gray-200 shadow-sm rounded-2xl">
            <form method="GET" action="{{ route('inventory.logs') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <!-- Filter by Product Dropdown -->
                <div class="md:col-span-5 space-y-1">
                    <label class="block text-xs font-black uppercase text-gray-600">
                        <i data-lucide="filter" class="w-3.5 h-3.5 inline mr-1 text-[#7A1C49]"></i> Filter By Product
                    </label>
                    <select name="product_id" onchange="this.form.submit()" class="form-select text-xs font-bold w-full rounded-xl border-gray-300">
                        <option value="">— All Products (Showing All Logs) —</option>
                        @foreach($allItems as $p)
                            <option value="{{ $p->id }}" {{ (string)($productId ?? '') === (string)$p->id ? 'selected' : '' }}>
                                {{ $p->item_name }} ({{ $p->item_code }}) — Stock: {{ $p->quantity }} {{ $p->unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter by Movement Type -->
                <div class="md:col-span-3 space-y-1">
                    <label class="block text-xs font-black uppercase text-gray-600">
                        <i data-lucide="layers" class="w-3.5 h-3.5 inline mr-1 text-[#7A1C49]"></i> Movement Type
                    </label>
                    <select name="action" onchange="this.form.submit()" class="form-select text-xs font-bold w-full rounded-xl border-gray-300">
                        <option value="">All Movement Types</option>
                        <option value="STOCK_IN" {{ ($movementType ?? '') === 'STOCK_IN' ? 'selected' : '' }}>Stock In</option>
                        <option value="RESTOCK" {{ ($movementType ?? '') === 'RESTOCK' ? 'selected' : '' }}>Restock</option>
                        <option value="RETURNED" {{ ($movementType ?? '') === 'RETURNED' ? 'selected' : '' }}>Returned</option>
                        <option value="STOCK_OUT" {{ ($movementType ?? '') === 'STOCK_OUT' ? 'selected' : '' }}>Stock Out</option>
                        <option value="SALE" {{ ($movementType ?? '') === 'SALE' ? 'selected' : '' }}>Sale (POS)</option>
                        <option value="DAMAGED" {{ ($movementType ?? '') === 'DAMAGED' ? 'selected' : '' }}>Damaged</option>
                        <option value="EXPIRED" {{ ($movementType ?? '') === 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                        <option value="TRANSFER" {{ ($movementType ?? '') === 'TRANSFER' ? 'selected' : '' }}>Transfer</option>
                        <option value="ADJUSTMENT" {{ ($movementType ?? '') === 'ADJUSTMENT' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="md:col-span-3 space-y-1">
                    <label class="block text-xs font-black uppercase text-gray-600">
                        <i data-lucide="search" class="w-3.5 h-3.5 inline mr-1 text-[#7A1C49]"></i> Search Keywords
                    </label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search remarks, SKU..." class="form-input text-xs font-semibold w-full rounded-xl border-gray-300">
                </div>

                <!-- Submit / Clear Buttons -->
                <div class="md:col-span-1 flex items-center gap-1">
                    <button type="submit" class="btn btn-primary text-xs py-2 px-3 w-full justify-center shadow-sm" title="Apply Filters">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    </button>
                    @if($productId || $movementType || $search)
                        <a href="{{ route('inventory.logs') }}" class="btn btn-secondary text-xs py-2 px-2.5 text-gray-500 hover:text-red-700" title="Reset all filters">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <!-- Product Logs Table -->
        <section class="card p-0 overflow-hidden shadow-sm border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b-2 border-gray-200">
                            <th class="px-4 py-3">Date & Time</th>
                            <th class="px-4 py-3">Product / SKU</th>
                            <th class="px-4 py-3">Movement Action</th>
                            <th class="px-4 py-3 text-center">Qty Change</th>
                            <th class="px-4 py-3 text-center">Stock Snapshot</th>
                            <th class="px-4 py-3">Recorded By</th>
                            <th class="px-4 py-3">Reason / Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm font-semibold">
                        @forelse($recentTransactions as $transaction)
                            <tr class="hover:bg-pink-50/30 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-gray-700 font-bold">
                                    {{ $transaction->created_at->format('M j, Y') }}
                                    <span class="block text-[11px] font-semibold text-gray-500">{{ $transaction->created_at->format('g:i A') }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($transaction->inventory)
                                        <a href="{{ route('inventory.logs', ['product_id' => $transaction->inventory_id]) }}" 
                                           class="font-extrabold text-[#7A1C49] hover:underline flex items-center gap-1"
                                           title="Filter logs specifically for {{ $transaction->inventory->item_name }}">
                                            <span>{{ $transaction->inventory->item_name }}</span>
                                            <i data-lucide="external-link" class="w-3 h-3 opacity-60"></i>
                                        </a>
                                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wide">
                                            {{ $transaction->inventory->item_code }} • {{ $transaction->inventory->category }}
                                        </span>
                                    @else
                                        <strong class="text-gray-500 italic">Deleted product</strong>
                                        <span class="block text-xs text-gray-400">SKU unavailable</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <?php
                                        $actionName = $transaction->action ?? $transaction->transaction_type;
                                        $badgeColor = match($actionName) {
                                            'STOCK_IN', 'RESTOCK', 'RETURNED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'SALE' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'DAMAGED', 'EXPIRED' => 'bg-red-100 text-red-800 border-red-200',
                                            'STOCK_OUT', 'TRANSFER' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            default => 'bg-blue-100 text-blue-800 border-blue-200',
                                        };
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black border {{ $badgeColor }}">
                                        {{ str_replace('_', ' ', $actionName) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-black {{ $transaction->quantity_change < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                    <span class="text-base">{{ $transaction->quantity_change > 0 ? '+' : '' }}{{ $transaction->quantity_change }}</span>
                                    <span class="text-xs font-bold text-gray-500 ml-0.5">{{ $transaction->inventory->unit ?? '' }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-xs text-gray-700">
                                    <span class="text-gray-500">{{ $transaction->previous_stock ?? '0' }}</span>
                                    <span class="text-gray-400 mx-1">→</span>
                                    <span class="text-gray-900 font-extrabold">{{ $transaction->new_stock ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-xs font-bold text-gray-700">
                                    {{ $transaction->user->name ?? 'System / Deleted User' }}
                                </td>
                                <td class="px-4 py-3.5 text-xs text-gray-600 font-medium max-w-xs">
                                    {{ $transaction->notes ?: '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-16 text-center text-gray-500 font-bold">
                                    <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                                    <div class="text-base text-gray-700">No product movement logs found</div>
                                    <p class="text-xs text-gray-500 mt-1 font-normal">Try selecting another product or resetting your search filters.</p>
                                    @if($productId || $movementType || $search)
                                        <a href="{{ route('inventory.logs') }}" class="btn btn-secondary text-xs mt-3 inline-flex items-center gap-1.5">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Clear All Filters
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentTransactions->hasPages())
                <div class="border-t border-gray-200 bg-gray-50 p-4">
                    {{ $recentTransactions->links() }}
                </div>
            @endif
        </section>
    @endif

    <!-- ADJUST STOCK MODAL (Usable across both Inventory and Logs views) -->
    <div x-show="adjustModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" style="display: none;" role="dialog" aria-modal="true">
        <div @click="adjustModal = false" class="fixed inset-0 modal-backdrop"></div>
        <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-md border border-gray-200 p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 mb-4 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="sliders" class="w-5 h-5 text-[#7A1C49]"></i>
                    <h3 class="text-xl font-extrabold text-[#7A1C49]">Adjust Stock</h3>
                </div>
                <button @click="adjustModal = false" class="text-gray-400 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <template x-if="selectedItem">
                <form :action="'{{ url('/inventory') }}/' + selectedItem.id + '/adjust'" method="POST" class="space-y-4">
                    @csrf
                    <div class="p-3 bg-pink-50 rounded-xl border border-pink-100">
                        <span class="text-xs font-black uppercase text-[#7A1C49]">Target Product</span>
                        <div class="font-extrabold text-base text-gray-900" x-text="selectedItem.item_name"></div>
                        <div class="text-xs font-bold text-gray-600 mt-0.5" x-text="'Current In-Stock: ' + selectedItem.quantity + ' ' + selectedItem.unit + ' (' + selectedItem.item_code + ')'"></div>
                    </div>
                    <label class="block text-sm font-extrabold text-gray-900">Movement Type
                        <select name="transaction_type" required class="form-select mt-1 font-bold w-full rounded-xl">
                            <option value="STOCK_IN">Stock In (Add New Stock)</option>
                            <option value="RESTOCK">Restock (Replenishment)</option>
                            <option value="RETURNED">Returned by Client / Supplier</option>
                            <option value="STOCK_OUT">Stock Out (Used in Salon)</option>
                            <option value="SALE">Sale (Retail)</option>
                            <option value="DAMAGED">Damaged / Broken</option>
                            <option value="EXPIRED">Expired</option>
                            <option value="TRANSFER">Transfer to Branch/Stylist</option>
                            <option value="ADJUSTMENT">Manual Adjustment (+/-)</option>
                        </select>
                    </label>
                    <label class="block text-sm font-extrabold text-gray-900">Quantity
                        <input type="number" name="quantity_change" min="-100000" max="100000" value="1" required class="form-input mt-1 font-bold w-full rounded-xl">
                        <span class="text-[11px] font-semibold text-gray-500 mt-1 block">Specify amount to add or subtract.</span>
                    </label>
                    <label class="block text-sm font-extrabold text-gray-900">Reason / Remarks
                        <input type="text" name="notes" placeholder="e.g. Replenishment from supplier shipment #812" required maxlength="1000" class="form-input mt-1 font-bold w-full rounded-xl">
                    </label>
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button type="button" @click="adjustModal = false" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary shadow">Save Movement & Update Stock</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- ADD INVENTORY ITEM MODAL -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" style="display: none;" role="dialog" aria-modal="true">
        <div @click="addModal = false" class="fixed inset-0 modal-backdrop"></div>
        <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-lg border border-gray-200 p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 mb-4 border-b">
                <div class="flex items-center gap-2">
                    <i data-lucide="package-plus" class="w-5 h-5 text-[#7A1C49]"></i>
                    <h3 class="text-xl font-extrabold text-[#7A1C49]">Add Inventory Item</h3>
                </div>
                <button @click="addModal = false" class="text-gray-400 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('inventory.store') }}" class="space-y-4">
                @csrf
                <label class="block text-sm font-extrabold text-gray-900">Item Name
                    <input type="text" name="item_name" placeholder="e.g. L'Oreal Professional Hair Dye 60ml" required class="form-input mt-1 font-bold w-full rounded-xl">
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="text-sm font-extrabold text-gray-900">Category
                        <input type="text" name="category" required value="Hair Supplies" class="form-input mt-1 font-bold w-full rounded-xl">
                    </label>
                    <label class="text-sm font-extrabold text-gray-900">Unit
                        <input type="text" name="unit" required value="pcs" placeholder="pcs, bottles, tubes" class="form-input mt-1 font-bold w-full rounded-xl">
                    </label>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <label class="text-sm font-extrabold text-gray-900">Initial Stock
                        <input type="number" name="quantity" min="0" value="0" required class="form-input mt-1 font-bold w-full rounded-xl">
                    </label>
                    <label class="text-sm font-extrabold text-gray-900">Minimum Stock Level
                        <input type="number" name="min_stock_level" min="1" value="4" required class="form-input mt-1 font-bold w-full rounded-xl">
                    </label>
                </div>
                <label class="block text-sm font-extrabold text-gray-900">Supplier
                    <input type="text" name="supplier" placeholder="e.g. Beauty Source Inc." class="form-input mt-1 font-bold w-full rounded-xl">
                </label>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" @click="addModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary shadow">Save Item to Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection