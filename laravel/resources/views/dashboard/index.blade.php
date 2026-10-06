@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header / Title -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold role-themed-text tracking-tight">Salon Dashboard</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Welcome back, {{ auth()->user()->name ?? auth()->user()->username }}! Here is your business overview for today.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('appointments.index') }}" class="btn btn-primary text-sm shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> New Appointment
            </a>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary text-sm shadow-sm">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Point of Sale
            </a>
        </div>
    </div>

    <!-- 4 KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Today Sales -->
        <div class="card border-l-4 border-l-[#7A1C49] flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-pink-100 text-[#7A1C49] flex items-center justify-center shrink-0">
                <i data-lucide="philippine-peso" class="w-7 h-7"></i>
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-gray-500 tracking-wider">Today's Revenue</span>
                <div class="text-2xl font-black text-gray-900">₱{{ number_format($todaySales, 2) }}</div>
            </div>
        </div>

        <!-- Today Appointments -->
        <div class="card border-l-4 border-l-blue-600 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                <i data-lucide="calendar" class="w-7 h-7"></i>
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-gray-500 tracking-wider">Today's Bookings</span>
                <div class="text-2xl font-black text-gray-900">{{ $todayAppointmentsCount }} Clients</div>
            </div>
        </div>

        <!-- Active Customers -->
        <div class="card border-l-4 border-l-emerald-600 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-7 h-7"></i>
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-gray-500 tracking-wider">Registered Clients</span>
                <div class="text-2xl font-black text-gray-900">{{ $totalCustomers }} Active</div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="card border-l-4 border-l-amber-600 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-7 h-7"></i>
            </div>
            <div>
                <span class="text-xs font-bold uppercase text-gray-500 tracking-wider">Stock Warnings</span>
                <div class="text-2xl font-black {{ $lowStockCount > 0 ? 'text-amber-700' : 'text-gray-900' }}">{{ $lowStockCount }} Low Items</div>
            </div>
        </div>
    </div>

    <!-- Monthly Profit Snapshot -->
    <div class="card bg-gradient-to-r from-pink-50 via-white to-pink-50 border-2 border-pink-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-black text-[#7A1C49] flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-5 h-5"></i> Monthly Financial Performance ({{ date('F Y') }})
                </h3>
                <p class="text-sm font-semibold text-gray-600">Overview of recorded salon sales vs operational expenses</p>
            </div>
            <div class="flex flex-wrap items-center gap-6">
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase">Gross Sales:</span>
                    <div class="text-lg font-black text-emerald-700">₱{{ number_format($monthlySales, 2) }}</div>
                </div>
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase">Expenses:</span>
                    <div class="text-lg font-black text-red-700">₱{{ number_format($monthlyExpenses, 2) }}</div>
                </div>
                <div class="border-l-2 border-pink-300 pl-6">
                    <span class="text-xs font-bold text-[#7A1C49] uppercase">Estimated Net Profit:</span>
                    <div class="text-xl font-black text-[#7A1C49]">₱{{ number_format($netProfit, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Today's Appointments (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="card">
                <div class="flex items-center justify-between mb-4 pb-3 border-b-2 border-gray-100">
                    <h2 class="text-xl font-extrabold text-[#7A1C49] flex items-center gap-2">
                        <i data-lucide="clock" class="w-5 h-5"></i> Today's Appointments Schedule
                    </h2>
                    <a href="{{ route('appointments.index') }}" class="text-sm font-bold text-[#7A1C49] hover:underline flex items-center gap-1">
                        View All <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>

                @if($todayAppointments->isEmpty())
                    <div class="text-center py-10 text-gray-500 font-bold">
                        <i data-lucide="calendar-check" class="w-12 h-12 mx-auto mb-2 text-gray-400"></i>
                        <p>No appointments booked for today yet.</p>
                        <a href="{{ route('appointments.index') }}" class="btn btn-primary btn-sm mt-3 inline-flex">Book Appointment</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b-2 border-gray-200">
                                    <th class="py-3 px-4">Time</th>
                                    <th class="py-3 px-4">Client</th>
                                    <th class="py-3 px-4">Stylist</th>
                                    <th class="py-3 px-4">Service</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm font-semibold">
                                @foreach($todayAppointments as $apt)
                                    <tr class="hover:bg-pink-50 transition">
                                        <td class="py-3 px-4 font-bold text-gray-900 whitespace-nowrap">
                                            {{ date('g:i A', strtotime($apt->start_time)) }} - {{ date('g:i A', strtotime($apt->end_time)) }}
                                        </td>
                                        <td class="py-3 px-4 font-extrabold text-gray-900">
                                            {{ $apt->customer->full_name ?? 'Walk-in' }}
                                        </td>
                                        <td class="py-3 px-4 text-gray-700">
                                            {{ $apt->employee->full_name ?? 'Any Stylist' }}
                                        </td>
                                        <td class="py-3 px-4 text-[#7A1C49] font-bold">
                                            {{ $apt->services->pluck('service_name')->join(', ') }}
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($apt->status === 'CONFIRMED')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-blue-100 text-blue-800 border border-blue-300">CONFIRMED</span>
                                            @elseif($apt->status === 'COMPLETED')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">COMPLETED</span>
                                            @elseif($apt->status === 'PENDING')
                                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-300">PENDING</span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-red-100 text-red-800 border border-red-300">CANCELLED</span>
                                            @endif
                                            <div class="mt-1 text-xs font-semibold text-gray-500">{{ str_replace('_', ' ', $apt->arrival_status ?? 'NOT_ARRIVED') }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex flex-wrap justify-end gap-2">
                                                @if($apt->status === 'PENDING')
                                                    <form method="POST" action="{{ route('appointments.status', $apt->id) }}">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="CONFIRMED">
                                                        <button type="submit" class="inline-flex min-h-10 items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-xs font-extrabold text-white shadow-sm transition hover:bg-blue-700" title="Confirm booking">
                                                            <i data-lucide="check" class="h-4 w-4"></i> Confirm
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($apt->status !== 'COMPLETED' && $apt->status !== 'CANCELLED' && !$apt->no_show)
                                                    @if(($apt->arrival_status ?? 'NOT_ARRIVED') === 'NOT_ARRIVED')
                                                        <form method="POST" action="{{ route('appointments.arrival', $apt) }}">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="arrival_status" value="ARRIVED">
                                                            <button type="submit" class="inline-flex min-h-10 items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-2 text-xs font-extrabold text-white">Mark Arrived</button>
                                                        </form>
                                                    @elseif($apt->arrival_status === 'ARRIVED')
                                                        <form method="POST" action="{{ route('appointments.arrival', $apt) }}">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="arrival_status" value="IN_SERVICE">
                                                            <button type="submit" class="inline-flex min-h-10 items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-xs font-extrabold text-white">Start Service</button>
                                                        </form>
                                                    @endif
                                                    @if(in_array($apt->arrival_status, ['ARRIVED', 'IN_SERVICE'], true))
                                                        <form method="POST" action="{{ route('appointments.arrival', $apt) }}">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="arrival_status" value="COMPLETED">
                                                            <button type="submit" class="inline-flex min-h-10 items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-extrabold text-white" title="Complete service">Complete</button>
                                                        </form>
                                                    @endif
                                                    <form method="POST" action="{{ route('appointments.status', $apt->id) }}" onsubmit="return confirm('Cancel this appointment?');">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="CANCELLED">
                                                        <button type="submit" class="inline-flex min-h-10 items-center gap-1.5 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-xs font-extrabold text-red-700 transition hover:bg-red-600 hover:text-white" title="Cancel appointment">
                                                            <i data-lucide="x" class="h-4 w-4"></i> Cancel
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Low Stock & Recent Sales (1 col) -->
        <div class="space-y-6">
            
            <!-- Low Stock Warnings -->
            @if($lowStockItems->isNotEmpty())
                <div class="card border-2 border-amber-300 bg-amber-50">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-extrabold text-amber-900 flex items-center gap-2">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i> Stock Replenishment Alert
                        </h3>
                        <a href="{{ route('inventory.index') }}" class="text-xs font-bold text-amber-800 hover:underline">Manage</a>
                    </div>
                    <ul class="space-y-2 text-sm font-semibold">
                        @foreach($lowStockItems as $item)
                            <li class="flex items-center justify-between bg-white p-2.5 rounded-xl border border-amber-200">
                                <span class="font-bold text-gray-800 truncate mr-2">{{ $item->item_name }}</span>
                                @php($stockLevel = \App\Models\Inventory::alertLevelForQuantity($item->quantity))
                                <span class="px-2 py-0.5 rounded text-xs font-black shrink-0 {{ $stockLevel === 'CRITICAL_STOCK' || $stockLevel === 'OUT_OF_STOCK' ? 'bg-red-200 text-red-900' : 'bg-amber-200 text-amber-900' }}">
                                    {{ $stockLevel === 'CRITICAL_STOCK' ? 'CRITICAL' : ($stockLevel === 'OUT_OF_STOCK' ? 'EMPTY' : 'LOW') }} · {{ $item->quantity }} {{ $item->unit }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Recent Completed Sales -->
            <div class="card">
                <div class="flex items-center justify-between mb-4 pb-2 border-b">
                    <h3 class="font-extrabold text-gray-900 flex items-center gap-2">
                        <i data-lucide="receipt" class="w-5 h-5 text-[#7A1C49]"></i> Recent Sales
                    </h3>
                    <a href="{{ route('sales.index') }}" class="text-xs font-bold text-[#7A1C49] hover:underline">View POS</a>
                </div>

                @if($recentSales->isEmpty())
                    <p class="text-gray-500 font-semibold text-sm text-center py-4">No recent sales transactions recorded.</p>
                @else
                    <ul class="divide-y divide-gray-100 text-sm font-semibold">
                        @foreach($recentSales as $sale)
                            <li class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-gray-900">{{ $sale->customer->full_name ?? 'Walk-in Customer' }}</div>
                                    <div class="text-xs text-gray-500">{{ $sale->invoice_code }} • {{ $sale->payment_method }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-black text-[#7A1C49]">₱{{ number_format($sale->final_amount, 2) }}</div>
                                    <div class="text-xs text-gray-400">{{ $sale->created_at->diffForHumans() }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
