@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">Salon Operational Expenses</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Track electric bills, water, rent, salon sanitizing supplies, and overhead costs.</p>
        </div>
        <button @click="addModal = true" class="btn btn-primary shadow-md">
            <i data-lucide="plus-circle" class="w-5 h-5"></i> Record New Expense
        </button>
    </div>

    <!-- Month Filter & Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="card p-5">
            <form method="GET" action="{{ route('expenses.index') }}">
                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Select Accounting Month</label>
                <input type="month" name="month" value="{{ $month }}" class="form-input text-base font-bold" onchange="this.form.submit()">
            </form>
        </div>

        <div class="card p-5 border-l-4 border-l-red-600">
            <span class="text-xs font-bold uppercase text-gray-500">Total Monthly Outflow</span>
            <div class="text-2xl font-black text-red-700 mt-1">₱{{ number_format($totalAmount, 2) }}</div>
        </div>

        <div class="card p-5 border-l-4 border-l-gray-600">
            <span class="text-xs font-bold uppercase text-gray-500">Recorded Transactions</span>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ $expenses->count() }} Records</div>
        </div>
    </div>

    <!-- Category Breakdown Pills -->
    @if($categoryBreakdown->isNotEmpty())
        <div class="card p-4">
            <span class="text-xs font-black uppercase text-gray-400 block mb-2">Category Spending Breakdown</span>
            <div class="flex flex-wrap gap-3">
                @foreach($categoryBreakdown as $catName => $catTotal)
                    <div class="px-3 py-1.5 rounded-xl bg-gray-100 border border-gray-200 text-xs font-bold text-gray-800 flex items-center gap-2">
                        <span>{{ $catName }}:</span>
                        <span class="font-black text-red-700">₱{{ number_format($catTotal, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Expenses Table -->
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b-2 border-gray-200">
                        <th class="py-3 px-4">Expense Date</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">Logged By</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm font-semibold">
                    @forelse($expenses as $exp)
                        <tr class="hover:bg-pink-50/50 transition">
                            <td class="py-4 px-4 font-bold text-gray-800 whitespace-nowrap">
                                {{ date('M d, Y', strtotime($exp->expense_date)) }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800 border">
                                    {{ $exp->expense_category }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-bold text-gray-900">
                                {{ $exp->description }}
                            </td>
                            <td class="py-4 px-4 text-xs font-bold text-gray-500">
                                {{ $exp->user->name ?? $exp->user->username ?? 'System' }}
                            </td>
                            <td class="py-4 px-4 text-right font-black text-red-700 text-base">
                                ₱{{ number_format($exp->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-gray-400 font-bold">
                                No expenses logged for this month.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Expense Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" style="display: none;" role="dialog" aria-modal="true">
        <!-- Background backdrop -->
        <div @click="addModal = false" class="fixed inset-0 modal-backdrop transition-opacity"></div>

        <div class="relative z-10 bg-white rounded-3xl text-left shadow-2xl w-full max-w-md border-2 border-gray-200 p-6 sm:p-8 max-h-[90vh] overflow-y-auto my-auto">
            <div class="flex items-center justify-between pb-3 mb-4 border-b">
                <h3 class="text-2xl font-extrabold text-[#7A1C49]">Log Expense</h3>
                <button @click="addModal = false" class="text-gray-400 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Expense Category</label>
                    <select name="expense_category" required class="form-select font-bold">
                        <option value="Utilities">Utilities (Electricity, Water, Internet)</option>
                        <option value="Salon Supplies">Salon Supplies & Disposables</option>
                        <option value="Rent">Salon Commercial Rent</option>
                        <option value="Maintenance">Equipment & Facility Maintenance</option>
                        <option value="Other">Other Miscellaneous</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Description / Vendor</label>
                    <input type="text" name="description" required placeholder="e.g. Meralco electricity statement" class="form-input font-bold">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Amount (₱)</label>
                        <input type="number" step="0.50" min="1" name="amount" required placeholder="1500.00" class="form-input font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Expense Date</label>
                        <input type="date" name="expense_date" value="{{ now()->toDateString() }}" required class="form-input font-bold">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" @click="addModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
