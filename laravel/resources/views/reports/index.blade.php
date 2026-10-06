@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">Financial & Salon Reports</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Audit date-filtered sales revenues, service breakdowns, and operating expenses.</p>
        </div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-secondary shadow-sm">
            <i data-lucide="download" class="w-5 h-5"></i> Export CSV
        </a><a href="{{ route('reports.export.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-secondary shadow-sm">
            <i data-lucide="file-spreadsheet" class="w-5 h-5"></i> Export Excel
        </a><a href="{{ route('reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-secondary shadow-sm">
            <i data-lucide="file-text" class="w-5 h-5"></i> Export PDF
        </a></div>
    </div>

    <!-- Date Range Filter -->
    <div class="card p-5">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-black uppercase text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input font-bold">
            </div>
            <div>
                <label class="block text-xs font-black uppercase text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input font-bold">
            </div>
            <div>
                <button type="submit" class="btn btn-primary w-full">Generate Audit Report</button>
            </div>
        </form>
    </div>

    <!-- Financial Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="card p-6 border-l-4 border-l-emerald-600">
            <span class="text-xs font-black uppercase text-gray-500">Gross Sales Revenue</span>
            <div class="text-2xl font-black text-emerald-700 mt-1">₱{{ number_format($totalRevenue, 2) }}</div>
            <span class="text-xs text-gray-400 font-bold mt-1 block">{{ $sales->count() }} transactions</span>
        </div>

        <div class="card p-6 border-l-4 border-l-red-600">
            <span class="text-xs font-black uppercase text-gray-500">Operating Expenses</span>
            <div class="text-2xl font-black text-red-700 mt-1">₱{{ number_format($totalExpenses, 2) }}</div>
            <span class="text-xs text-gray-400 font-bold mt-1 block">{{ $expenses->count() }} bills logged</span>
        </div>

        <div class="card p-6 border-l-4 border-l-[#7A1C49]">
            <span class="text-xs font-black uppercase text-gray-500">Net Salon Income</span>
            <div class="text-2xl font-black text-[#7A1C49] mt-1">₱{{ number_format($netIncome, 2) }}</div>
            <span class="text-xs text-gray-400 font-bold mt-1 block">Net Profit / Loss</span>
        </div>
    </div>

    <!-- Sales Transactions Detail -->
    <div class="card p-0 overflow-hidden">
        <div class="p-5 border-b-2 border-gray-100">
            <h3 class="text-xl font-extrabold text-[#7A1C49] flex items-center gap-2">
                <i data-lucide="receipt" class="w-5 h-5"></i> Period Sales Log ({{ $sales->count() }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b">
                        <th class="py-3 px-4">Invoice</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Client</th>
                        <th class="py-3 px-4">Stylist</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-semibold">
                    @forelse($sales as $s)
                        <tr class="hover:bg-pink-50/50">
                            <td class="py-3 px-4 font-black text-[#7A1C49]">{{ $s->invoice_code }}</td>
                            <td class="py-3 px-4 text-xs font-bold text-gray-600">{{ $s->created_at->format('M d, Y h:i A') }}</td>
                            <td class="py-3 px-4 font-bold text-gray-900">{{ $s->customer->full_name ?? 'Walk-in' }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $s->employee->full_name ?? 'Salon Attendant' }}</td>
                            <td class="py-3 px-4 text-xs font-black uppercase">{{ $s->payment_method }}</td>
                            <td class="py-3 px-4 text-right font-black text-emerald-700">₱{{ number_format($s->final_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 text-gray-400 font-bold">No sales in this timeframe.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
