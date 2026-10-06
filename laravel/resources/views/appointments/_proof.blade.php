@php
    $statusClass = match ($appointment->status) {
        'CONFIRMED' => 'bg-blue-100 text-blue-800',
        'COMPLETED' => 'bg-emerald-100 text-emerald-800',
        'PENDING' => 'bg-amber-100 text-amber-800',
        default => 'bg-red-100 text-red-800',
    };
@endphp

<div class="appointment-proof rounded-xl border border-[#e8c36c] bg-[#fffdf7] p-4 text-left shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-[#eadfbf] pb-3">
        <div>
            <p class="text-xs font-black uppercase tracking-widest text-[#7A1C49]">Appointment proof</p>
            <p class="mt-1 text-lg font-black text-gray-900">{{ $appointment->appointment_code }}</p>
        </div>
        <span class="rounded-full px-3 py-1 text-xs font-black {{ $statusClass }}">{{ $appointment->status }}</span>
    </div>

    <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
        <div><span class="block text-xs font-bold uppercase text-gray-500">Customer</span><strong class="text-gray-900">{{ $appointment->customer->full_name ?? 'Walk-in Customer' }}</strong></div>
        <div><span class="block text-xs font-bold uppercase text-gray-500">Contact number</span><strong class="text-gray-900">{{ $appointment->customer->phone ?? 'N/A' }}</strong></div>
        <div><span class="block text-xs font-bold uppercase text-gray-500">Address</span><strong class="text-gray-900">{{ $appointment->customer->address ?? 'N/A' }}</strong></div>
        <div><span class="block text-xs font-bold uppercase text-gray-500">Appointment date</span><strong class="text-gray-900">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('F d, Y') }}</strong></div>
        <div><span class="block text-xs font-bold uppercase text-gray-500">Time</span><strong class="text-gray-900">{{ date('g:i A', strtotime($appointment->start_time)) }} - {{ date('g:i A', strtotime($appointment->end_time)) }}</strong></div>
        <div><span class="block text-xs font-bold uppercase text-gray-500">Staff / stylist</span><strong class="text-gray-900">{{ $appointment->employee->full_name ?? 'Unassigned' }}</strong></div>
    </div>

    <div class="mt-4 border-t border-[#eadfbf] pt-3">
        <span class="block text-xs font-bold uppercase text-gray-500">Services</span>
        <p class="mt-1 font-bold text-[#7A1C49]">{{ $appointment->services->pluck('service_name')->join(', ') }}</p>
        <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
            <span class="text-sm font-bold text-gray-500">Total appointment amount</span>
            <strong class="text-lg text-gray-900">₱{{ number_format($appointment->total_amount, 2) }}</strong>
        </div>
    </div>

    @if($appointment->notes)
        <p class="mt-3 rounded-lg bg-amber-50 p-2 text-xs font-semibold text-amber-900">Note: {{ $appointment->notes }}</p>
    @endif

    <button
        type="button"
        class="download-appointment-proof mt-4 inline-flex items-center gap-2 rounded-lg bg-[#7A1C49] px-3 py-2 text-xs font-bold text-white transition hover:bg-[#5c0e2a]"
        onclick="downloadAppointmentProof(this)"
        data-proof-code="{{ $appointment->appointment_code }}"
        data-proof-status="{{ $appointment->status }}"
        data-proof-name="{{ $appointment->customer->full_name ?? 'Walk-in Customer' }}"
        data-proof-phone="{{ $appointment->customer->phone ?? 'N/A' }}"
        data-proof-address="{{ $appointment->customer->address ?? 'N/A' }}"
        data-proof-date="{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('F d, Y') }}"
        data-proof-time="{{ date('g:i A', strtotime($appointment->start_time)) }} - {{ date('g:i A', strtotime($appointment->end_time)) }}"
        data-proof-staff="{{ $appointment->employee->full_name ?? 'Unassigned' }}"
        data-proof-services="{{ $appointment->services->pluck('service_name')->join(', ') }}"
        data-proof-total="₱{{ number_format($appointment->total_amount, 2) }}"
    >
        <i data-lucide="download" class="h-4 w-4"></i>
        Download proof image
    </button>
</div>