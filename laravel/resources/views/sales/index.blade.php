@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="posSystem()">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">Point of Sale (POS)</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Process client payments, bill hair & nail services, sell salon retail products, and earn loyalty points.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-pink-100 text-[#7A1C49] font-black text-sm border border-pink-300">
                Salon Register Active
            </span>
        </div>
    </div>

    <!-- POS Main Grid: Items Catalog (Left 2 cols) & Cart Checkout (Right 1 col) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Services & Retail Products Catalog -->
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-5">
                <!-- Catalog Header Tabs & Search -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b-2 border-gray-100">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="activeTab = 'services'" :class="activeTab === 'services' ? 'bg-[#7A1C49] text-white' : 'bg-gray-100 text-gray-700 hover:bg-pink-50'" class="px-4 py-2 rounded-xl font-extrabold text-sm transition">
                            <i data-lucide="scissors" class="w-4 h-4 inline mr-1"></i> Salon Services
                        </button>
                        <button type="button" @click="activeTab = 'products'" :class="activeTab === 'products' ? 'bg-[#7A1C49] text-white' : 'bg-gray-100 text-gray-700 hover:bg-pink-50'" class="px-4 py-2 rounded-xl font-extrabold text-sm transition">
                            <i data-lucide="package" class="w-4 h-4 inline mr-1"></i> Retail Products
                        </button>
                    </div>

                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Search item..." class="form-input text-sm py-2 pl-9 pr-3 rounded-xl">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Services Grid -->
                <div x-show="activeTab === 'services'" class="pt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[550px] overflow-y-auto">
                    @foreach($services as $srv)
                        <div x-show="matchesSearch('{{ strtolower($srv->service_name) }}', '{{ strtolower($srv->category) }}')"
                             class="p-4 rounded-2xl border-2 border-gray-200 hover:border-[#7A1C49] bg-white transition cursor-pointer flex flex-col justify-between"
                             @click="addItem('SERVICE', {{ $srv->id }}, '{{ addslashes($srv->service_name) }}', {{ $srv->price }})">
                            <div>
                                <span class="text-xs font-bold uppercase text-gray-500">{{ $srv->category }}</span>
                                <h4 class="text-base font-extrabold text-gray-900 mt-0.5 leading-snug">{{ $srv->service_name }}</h4>
                                <span class="text-xs text-gray-500 font-semibold">{{ $srv->duration_minutes }} mins</span>
                            </div>
                            <div class="mt-3 flex items-center justify-between pt-2 border-t border-gray-100">
                                <span class="text-lg font-black text-[#7A1C49]">₱{{ number_format($srv->price, 2) }}</span>
                                <span class="px-2.5 py-1 rounded-lg bg-pink-100 text-[#7A1C49] text-xs font-black hover:bg-[#7A1C49] hover:text-white transition">
                                    + Add
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Products Grid -->
                <div x-show="activeTab === 'products'" style="display: none;" class="pt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[550px] overflow-y-auto">
                    @foreach($products as $prod)
                        <div x-show="matchesSearch('{{ strtolower($prod->item_name) }}', '{{ strtolower($prod->category) }}')"
                             class="p-4 rounded-2xl border-2 border-gray-200 hover:border-[#7A1C49] bg-white transition cursor-pointer flex flex-col justify-between"
                             @click="addItem('PRODUCT', {{ $prod->id }}, '{{ addslashes($prod->item_name) }}', 350.00)">
                            <div>
                                <span class="text-xs font-bold uppercase text-gray-500">{{ $prod->category }}</span>
                                <h4 class="text-base font-extrabold text-gray-900 mt-0.5 leading-snug">{{ $prod->item_name }}</h4>
                                <span class="text-xs text-emerald-700 font-bold">{{ $prod->quantity }} {{ $prod->unit }} available</span>
                            </div>
                            <div class="mt-3 flex items-center justify-between pt-2 border-t border-gray-100">
                                <span class="text-lg font-black text-[#7A1C49]">₱350.00</span>
                                <span class="px-2.5 py-1 rounded-lg bg-pink-100 text-[#7A1C49] text-xs font-black hover:bg-[#7A1C49] hover:text-white transition">
                                    + Add
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Current Cart & Checkout -->
        <div class="space-y-4">
            <div class="card border-2 border-[#7A1C49] p-5 shadow-lg">
                <div class="flex items-center justify-between pb-3 border-b-2 border-gray-100">
                    <h3 class="text-xl font-extrabold text-[#7A1C49] flex items-center gap-2">
                        <i data-lucide="receipt" class="w-5 h-5"></i> Current Bill
                    </h3>
                    <button type="button" @click="clearCart()" x-show="cart.length > 0" class="text-xs font-bold text-red-600 hover:underline">
                        Clear All
                    </button>
                </div>

                <form method="POST" action="{{ route('sales.store') }}" class="space-y-4 mt-3">
                    @csrf

                    <!-- Customer Selection -->
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-700 mb-1">Customer (Optional)</label>
                        <select name="customer_id" class="form-select text-sm font-bold">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Stylist Commission Attributed -->
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-700 mb-1">Stylist / Attendant</label>
                        <select name="employee_id" class="form-select text-sm font-bold">
                            <option value="">None / Salon Counter</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cart Item Rows -->
                    <div class="max-h-56 overflow-y-auto border-2 border-gray-200 rounded-xl p-2 space-y-2 bg-gray-50">
                        <template x-if="cart.length === 0">
                            <div class="text-center py-8 text-gray-400 font-bold text-sm">
                                <i data-lucide="shopping-bag" class="w-8 h-8 mx-auto mb-1 text-gray-300"></i>
                                Cart is empty. Click items on the left to add.
                            </div>
                        </template>

                        <template x-for="(item, index) in cart" :key="index">
                            <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-gray-200 text-sm">
                                <div class="flex-1 min-w-0 mr-2">
                                    <div class="font-extrabold text-gray-900 truncate" x-text="item.item_name"></div>
                                    <div class="text-xs text-gray-500" x-text="'₱' + item.unit_price.toFixed(2) + ' each'"></div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <!-- Qty Adjust -->
                                    <button type="button" @click="updateQty(index, -1)" class="w-6 h-6 rounded bg-gray-200 text-gray-800 font-black flex items-center justify-center hover:bg-gray-300">-</button>
                                    <span class="font-black text-sm w-5 text-center" x-text="item.quantity"></span>
                                    <button type="button" @click="updateQty(index, 1)" class="w-6 h-6 rounded bg-gray-200 text-gray-800 font-black flex items-center justify-center hover:bg-gray-300">+</button>
                                    
                                    <span class="font-black text-[#7A1C49] w-16 text-right" x-text="'₱' + (item.quantity * item.unit_price).toFixed(2)"></span>

                                    <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 ml-1">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                <!-- Hidden Inputs for Backend Form Submission -->
                                <input type="hidden" :name="'items[' + index + '][item_type]'" :value="item.item_type">
                                <input type="hidden" :name="'items[' + index + '][item_id]'" :value="item.item_id">
                                <input type="hidden" :name="'items[' + index + '][item_name]'" :value="item.item_name">
                                <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                                <input type="hidden" :name="'items[' + index + '][unit_price]'" :value="item.unit_price">
                            </div>
                        </template>
                    </div>

                    <!-- Discount & Total -->
                    <div class="space-y-2 pt-2 border-t-2 border-gray-100">
                        <div class="flex justify-between items-center text-sm font-bold text-gray-600">
                            <span>Subtotal:</span>
                            <span x-text="'₱' + subtotal().toFixed(2)">₱0.00</span>
                        </div>

                        <div class="flex justify-between items-center text-sm font-bold text-gray-600">
                            <label for="discount" class="text-xs uppercase font-black">Discount (%):</label>
                            <div class="flex items-center gap-2">
                                <input id="discount" type="number" step="0.01" min="0" max="100" name="discount_percentage" x-model.number="discountPercentage" class="form-input text-right text-sm py-1 px-2 w-24 font-bold" value="0">
                                <span class="text-xs font-black text-gray-500" x-text="'₱' + discountAmount().toFixed(2)">₱0.00</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-xl font-black text-[#7A1C49] pt-2 border-t border-gray-200">
                            <span>Final Total:</span>
                            <span x-text="'₱' + total().toFixed(2)">₱0.00</span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-700 mb-1">Payment Method</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2 rounded-xl border-2 border-gray-300 hover:border-[#7A1C49] cursor-pointer bg-white text-xs font-extrabold">
                                <input type="radio" name="payment_method" value="CASH" checked class="text-[#7A1C49] focus:ring-[#7A1C49]"> Cash
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-xl border-2 border-gray-300 hover:border-[#7A1C49] cursor-pointer bg-white text-xs font-extrabold">
                                <input type="radio" name="payment_method" value="GCASH" class="text-[#7A1C49] focus:ring-[#7A1C49]"> GCash
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-xl border-2 border-gray-300 hover:border-[#7A1C49] cursor-pointer bg-white text-xs font-extrabold">
                                <input type="radio" name="payment_method" value="MAYA" class="text-[#7A1C49] focus:ring-[#7A1C49]"> Maya
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-xl border-2 border-gray-300 hover:border-[#7A1C49] cursor-pointer bg-white text-xs font-extrabold">
                                <input type="radio" name="payment_method" value="CARD" class="text-[#7A1C49] focus:ring-[#7A1C49]"> Card
                            </label>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <button type="submit" :disabled="cart.length === 0" class="btn btn-primary w-full py-3.5 text-lg font-black shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="check-circle" class="w-5 h-5"></i> Complete Sale
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

<script>
    function posSystem() {
        return {
            activeTab: 'services',
            searchQuery: '',
            cart: [],
            discountPercentage: 0,
            matchesSearch(name, category) {
                if (!this.searchQuery) return true;
                const q = this.searchQuery.toLowerCase();
                return name.includes(q) || category.includes(q);
            },
            addItem(type, id, name, price) {
                const existing = this.cart.find(i => i.item_type === type && i.item_id === id);
                if (existing) {
                    existing.quantity++;
                } else {
                    this.cart.push({
                        item_type: type,
                        item_id: id,
                        item_name: name,
                        unit_price: parseFloat(price),
                        quantity: 1
                    });
                }
            },
            updateQty(index, change) {
                this.cart[index].quantity += change;
                if (this.cart[index].quantity <= 0) {
                    this.removeItem(index);
                }
            },
            removeItem(index) {
                this.cart.splice(index, 1);
            },
            clearCart() {
                this.cart = [];
                this.discountPercentage = 0;
            },
            subtotal() {
                return this.cart.reduce((acc, item) => acc + (item.quantity * item.unit_price), 0);
            },
            discountAmount() {
                const percentage = Math.min(100, Math.max(0, this.discountPercentage || 0));
                return this.subtotal() * (percentage / 100);
            },
            total() {
                return Math.max(0, this.subtotal() - this.discountAmount());
            }
        };
    }
</script>
@endsection
