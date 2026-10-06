@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ modalOpen: false }">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold role-themed-text tracking-tight">Appointments Booking</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Manage customer bookings, schedule stylists, and track appointment statuses.</p>
        </div>
        <button @click="modalOpen = true" class="btn btn-primary shadow-md">
            <i data-lucide="calendar-plus" class="w-5 h-5"></i> Schedule New Appointment
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="card p-4">
        <form method="GET" action="{{ route('appointments.index') }}" class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Appointment Date</label>
                <input type="date" name="date" value="{{ $date }}" class="form-input text-base font-bold" onchange="this.form.submit()">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Status Filter</label>
                <select name="status" class="form-select text-base font-bold" onchange="this.form.submit()">
                    <option value="ALL" {{ $status === 'ALL' || empty($status) ? 'selected' : '' }}>All Statuses</option>
                    <option value="PENDING" {{ $status === 'PENDING' ? 'selected' : '' }}>Pending Confirmation</option>
                    <option value="CONFIRMED" {{ $status === 'CONFIRMED' ? 'selected' : '' }}>Confirmed</option>
                    <option value="COMPLETED" {{ $status === 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                    <option value="CANCELLED" {{ $status === 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Stylist Filter</label>
                <select name="employee_id" class="form-select text-base font-bold" onchange="this.form.submit()">
                    <option value="ALL">All Stylists</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ (string)$employeeId === (string)$emp->id ? 'selected' : '' }}>
                            {{ $emp->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('appointments.index', ['date' => now()->toDateString()]) }}" class="btn btn-secondary flex-1 text-sm font-bold">
                    Today
                </a>
                <a href="{{ route('appointments.index', ['date' => now()->addDay()->toDateString()]) }}" class="btn btn-secondary flex-1 text-sm font-bold">
                    Tomorrow
                </a>
            </div>
        </form>
    </div>

    <!-- Appointments Table / Cards -->
    <div class="card overflow-hidden p-0">
        @if($appointments->isEmpty())
            <div class="text-center py-16 text-gray-500 font-bold">
                <i data-lucide="calendar-x" class="w-16 h-16 mx-auto mb-3 text-gray-400"></i>
                <h3 class="text-xl font-extrabold text-gray-700 mb-1">No Appointments Found</h3>
                <p class="text-gray-500 max-w-sm mx-auto">There are no appointments matching the selected filters for {{ date('F d, Y', strtotime($date)) }}.</p>
                <button @click="modalOpen = true" class="btn btn-primary mt-4 inline-flex">Schedule First Booking</button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b-2 border-gray-200">
                            <th class="py-3 px-4">Code & Time</th>
                            <th class="py-3 px-4">Client Details</th>
                            <th class="py-3 px-4">Assigned Stylist</th>
                            <th class="py-3 px-4">Services Booked</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm font-semibold">
                        @foreach($appointments as $apt)
                            <tr class="hover:bg-pink-50/50 transition">
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-black text-[#7A1C49]">{{ $apt->appointment_code }}</div>
                                    <div class="text-sm font-extrabold text-gray-900 mt-0.5">
                                        {{ date('g:i A', strtotime($apt->start_time)) }} - {{ date('g:i A', strtotime($apt->end_time)) }}
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="font-extrabold text-gray-900 text-base">{{ $apt->customer->full_name ?? 'Walk-in Customer' }}</div>
                                    <div class="text-xs text-gray-500 font-bold flex items-center gap-1 mt-0.5">
                                        <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                        @if($apt->customer?->phone)
                                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $apt->customer->phone) }}">{{ $apt->customer->phone }}</a>
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                    @if($apt->notes)
                                        <div class="text-xs text-amber-800 bg-amber-50 rounded px-2 py-0.5 mt-1 border border-amber-200 inline-block">
                                            Note: {{ $apt->notes }}
                                        </div>
                                    @endif
                                </td>

                                <td class="py-4 px-4">
                                    <span class="font-bold text-gray-800">{{ $apt->employee->full_name ?? 'Unassigned' }}</span>
                                    <div class="text-xs text-gray-500">{{ $apt->employee->position ?? 'Stylist' }}</div>
                                </td>

                                <td class="py-4 px-4">
                                    @php($durationMinutes = \Carbon\Carbon::parse($apt->start_time)->diffInMinutes(\Carbon\Carbon::parse($apt->end_time)))
                                    <ul class="list-disc list-inside text-sm text-[#7A1C49] font-bold">
                                        @foreach($apt->services as $srv)
                                            <li>{{ $srv->service_name }} (₱{{ number_format($srv->pivot->price_at_booking, 2) }})</li>
                                        @endforeach
                                    </ul>
                                    <div class="mt-1 text-xs font-semibold text-gray-500">{{ $durationMinutes }} minutes reserved</div>
                                </td>

                                <td class="py-4 px-4 font-black text-gray-900 text-base">
                                    ₱{{ number_format($apt->total_amount, 2) }}
                                </td>

                                <td class="py-4 px-4">
                                    @if($apt->no_show)
                                        <span class="px-3 py-1 rounded-full text-xs font-black bg-red-200 text-red-900 border border-red-400">NO SHOW</span>
                                    @elseif($apt->status === 'CONFIRMED')
                                        <span class="px-3 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-800 border border-blue-300">CONFIRMED</span>
                                    @elseif($apt->status === 'COMPLETED')
                                        <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">COMPLETED</span>
                                    @elseif($apt->status === 'PENDING')
                                        <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">PENDING</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-black bg-red-100 text-red-800 border border-red-300">CANCELLED</span>
                                    @endif
                                    <div class="mt-2 text-xs font-bold text-gray-600">
                                        Arrival: {{ str_replace('_', ' ', $apt->arrival_status ?? 'NOT_ARRIVED') }}
                                    </div>
                                    @if($apt->arrival_time)
                                        <div class="text-xs text-gray-500">{{ $apt->arrival_time->format('g:i A') }} arrival</div>
                                        <div class="text-xs text-gray-500">Marked by {{ $apt->arrivalMarker->name ?? 'staff member' }}</div>
                                    @endif
                                </td>

                                <td class="py-4 px-4 text-right align-top">
                                    <div class="flex min-w-44 flex-col items-stretch justify-end gap-2">
                                        <details class="relative text-left">
                                            <summary class="cursor-pointer rounded-lg border border-[#e8c36c] bg-[#fff8e7] px-2.5 py-1.5 text-xs font-bold text-[#7A1C49]">View proof</summary>
                                            <div class="fixed right-4 top-24 z-50 max-h-[calc(100vh-8rem)] w-[min(90vw,28rem)] overflow-y-auto rounded-xl bg-white shadow-2xl">
                                                @include('appointments._proof', ['appointment' => $apt])
                                            </div>
                                        </details>
                                        @if($apt->status === 'PENDING')
                                            <form method="POST" action="{{ route('appointments.status', $apt->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="CONFIRMED">
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white font-bold text-xs border border-blue-300 transition" title="Confirm Booking">
                                                    Confirm
                                                </button>
                                            </form>
                                        @endif

                                        @if($apt->status !== 'COMPLETED' && $apt->status !== 'CANCELLED' && !$apt->no_show)
                                            @if(($apt->arrival_status ?? 'NOT_ARRIVED') === 'NOT_ARRIVED')
                                                <form method="POST" action="{{ route('appointments.arrival', $apt) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="arrival_status" value="ARRIVED">
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-600 hover:text-white font-bold text-xs border border-amber-300 transition">Mark Customer Arrived</button>
                                                </form>
                                            @elseif(($apt->arrival_status ?? '') === 'ARRIVED')
                                                <form method="POST" action="{{ route('appointments.arrival', $apt) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="arrival_status" value="IN_SERVICE">
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white font-bold text-xs border border-blue-300 transition">Start Service</button>
                                                </form>
                                            @endif
                                            @if(in_array($apt->arrival_status, ['ARRIVED', 'IN_SERVICE'], true))
                                                <form method="POST" action="{{ route('appointments.arrival', $apt) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="arrival_status" value="COMPLETED">
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white font-bold text-xs border border-emerald-300 transition">Complete Service</button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('appointments.status', $apt->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="CANCELLED">
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-600 hover:text-white font-bold text-xs border border-red-300 transition" onclick="return confirm('Cancel this appointment?')" title="Cancel">
                                                    Cancel
                                                </button>
                                            </form>

                                            @if(($apt->arrival_status ?? 'NOT_ARRIVED') === 'NOT_ARRIVED')
                                                <form method="POST" action="{{ route('appointments.status', $apt->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="CANCELLED">
                                                    <input type="hidden" name="no_show" value="1">
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-600 hover:text-white font-bold text-xs border border-red-300 transition" onclick="return confirm('Mark this appointment as a no-show?')" title="Mark No Show">No Show</button>
                                                </form>
                                            @endif
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

    <!-- Schedule Appointment Modal -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" style="display: none;" role="dialog" aria-modal="true">
        <!-- Background backdrop -->
        <div @click="modalOpen = false" class="fixed inset-0 modal-backdrop transition-opacity"></div>

        <!-- Modal Box -->
        <div class="relative z-10 bg-white rounded-3xl text-left shadow-2xl w-full max-w-xl border-2 border-gray-200 p-6 sm:p-8 max-h-[90vh] overflow-y-auto my-auto">
                <div class="flex items-center justify-between pb-4 mb-4 border-b-2 border-gray-100">
                    <h3 class="text-2xl font-extrabold text-[#7A1C49] flex items-center gap-2">
                        <i data-lucide="calendar-plus" class="w-6 h-6"></i> Book Salon Appointment
                    </h3>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-700 p-1 rounded-lg">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('appointments.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Select Customer</label>
                        <select name="customer_id" required class="form-select font-bold">
                            <option value="">-- Choose Registered Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Assign Stylist</label>
                        <select name="employee_id" required class="form-select font-bold">
                            <option value="">-- Choose Stylist --</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->position }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-extrabold text-gray-900 mb-1">Appointment Date</label>
                            <input type="date" name="appointment_date" value="{{ $date }}" required class="form-input font-bold">
                        </div>
                        <div>
                            <label class="block text-sm font-extrabold text-gray-900 mb-1">Start Time</label>
                            <input type="time" name="start_time" value="10:00" required class="form-input font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-2">Select Services to Perform</label>
                        <div class="max-h-48 overflow-y-auto border-2 border-gray-300 rounded-xl p-3 space-y-2 bg-gray-50">
                            @foreach($services as $srv)
                                <label class="flex items-center justify-between p-2 rounded-lg bg-white border border-gray-200 hover:border-[#7A1C49] cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="service_ids[]" value="{{ $srv->id }}" class="w-5 h-5 rounded border-2 border-gray-400 text-[#7A1C49] focus:ring-[#7A1C49]">
                                        <div>
                                            <span class="font-extrabold text-gray-900 text-sm">{{ $srv->service_name }}</span>
                                            <span class="text-xs text-gray-500 block">{{ $srv->category }} • {{ $srv->duration_minutes }} mins</span>
                                        </div>
                                    </div>
                                    <span class="font-black text-[#7A1C49] text-sm">₱{{ number_format($srv->price, 2) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Special Client Notes / Requests</label>
                        <textarea name="notes" rows="2" class="form-textarea" placeholder="e.g. Skin sensitivity, specific hair color shade..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="modalOpen = false" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm Booking</button>
                    </div>
                </form>
            </div>
        </div>

</div>
@endsection
