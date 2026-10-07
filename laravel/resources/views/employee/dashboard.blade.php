@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-widest text-emerald-700">Staff workspace</p>
            <h1 class="mt-1 text-3xl font-extrabold role-themed-text tracking-tight">My Workday</h1>
            <p class="mt-1 font-semibold text-gray-600">Good day, {{ $employee->full_name }}. Here is your schedule for {{ $todayAppointments->isEmpty() ? now()->format('F j, Y') : \Carbon\Carbon::today()->format('F j, Y') }}.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('appointments.index', ['date' => now()->toDateString()]) }}" class="btn btn-primary text-sm"><i data-lucide="calendar-check" class="h-4 w-4"></i> Manage appointments</a>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary text-sm"><i data-lucide="shopping-cart" class="h-4 w-4"></i> Open POS</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card border-l-4 border-l-emerald-600 flex items-center gap-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700"><i data-lucide="calendar-days" class="h-7 w-7"></i></div><div><span class="text-xs font-bold uppercase tracking-wider text-gray-500">Today’s appointments</span><div class="text-2xl font-black text-gray-900">{{ $todayAppointments->count() }}</div></div></div>
        <div class="card border-l-4 border-l-amber-600 flex items-center gap-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700"><i data-lucide="clock-3" class="h-7 w-7"></i></div><div><span class="text-xs font-bold uppercase tracking-wider text-gray-500">Needs confirmation</span><div class="text-2xl font-black text-gray-900">{{ $pendingCount }}</div></div></div>
        <div class="card border-l-4 border-l-blue-600 flex items-center gap-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-700"><i data-lucide="calendar-plus" class="h-7 w-7"></i></div><div><span class="text-xs font-bold uppercase tracking-wider text-gray-500">Upcoming visits</span><div class="text-2xl font-black text-gray-900">{{ $upcomingAppointmentsCount }}</div></div></div>
        <div class="card border-l-4 border-l-[#7A1C49] flex items-center gap-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-pink-100 text-[#7A1C49]"><i data-lucide="philippine-peso" class="h-7 w-7"></i></div><div><span class="text-xs font-bold uppercase tracking-wider text-gray-500">Today’s sales</span><div class="text-2xl font-black text-gray-900">₱{{ number_format($todaySales, 2) }}</div></div></div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="card overflow-hidden p-0 lg:col-span-2">
            <div class="flex items-center justify-between border-b-2 border-gray-100 px-6 py-4"><div><h2 class="flex items-center gap-2 text-xl font-extrabold text-emerald-800"><i data-lucide="clock" class="h-5 w-5"></i> Today’s schedule</h2><p class="mt-1 text-sm font-semibold text-gray-500">Keep each visit moving smoothly.</p></div><a href="{{ route('appointments.index', ['date' => now()->toDateString(), 'employee_id' => $employee->id]) }}" class="text-sm font-bold text-emerald-700 hover:underline">View all</a></div>
            @forelse($todayAppointments as $appointment)
                <div class="border-b border-gray-100 px-6 py-5 last:border-b-0">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><span class="font-black text-gray-900">{{ date('g:i A', strtotime($appointment->start_time)) }} - {{ date('g:i A', strtotime($appointment->end_time)) }}</span><span class="rounded-full px-2.5 py-1 text-xs font-black {{ $appointment->status === 'CONFIRMED' ? 'bg-blue-100 text-blue-800' : ($appointment->status === 'PENDING' ? 'bg-amber-100 text-amber-800' : ($appointment->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800')) }}">{{ $appointment->status }}</span></div><p class="mt-1 text-lg font-extrabold text-gray-900">{{ $appointment->customer->full_name ?? 'Walk-in Customer' }}</p><p class="text-sm font-semibold text-emerald-800">{{ $appointment->services->pluck('service_name')->join(', ') }}</p></div>
                        <div class="flex flex-wrap gap-2">
                            @if($appointment->status !== 'COMPLETED' && $appointment->status !== 'CANCELLED' && !$appointment->no_show)
                                @if($appointment->status === 'PENDING')
                                    <form method="POST" action="{{ route('appointments.status', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="CONFIRMED"><button class="inline-flex min-h-10 items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-xs font-extrabold text-white hover:bg-blue-700">Confirm Legacy Booking</button></form>
                                @endif
                                @if(($appointment->arrival_status ?? 'NOT_ARRIVED') === 'NOT_ARRIVED')
                                    <form method="POST" action="{{ route('appointments.arrival', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="arrival_status" value="ARRIVED"><button class="inline-flex min-h-10 items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-2 text-xs font-extrabold text-white hover:bg-amber-700">Mark Customer Arrived</button></form>
                                @elseif($appointment->arrival_status === 'ARRIVED')
                                    <form method="POST" action="{{ route('appointments.arrival', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="arrival_status" value="IN_SERVICE"><button class="inline-flex min-h-10 items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-xs font-extrabold text-white hover:bg-blue-700">Start Service</button></form>
                                @endif
                                @if(in_array($appointment->arrival_status, ['ARRIVED', 'IN_SERVICE'], true))
                                    <form method="POST" action="{{ route('appointments.arrival', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="arrival_status" value="COMPLETED"><button class="inline-flex min-h-10 items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-extrabold text-white hover:bg-emerald-700">Complete Service</button></form>
                                @endif
                                <form method="POST" action="{{ route('appointments.status', $appointment) }}" onsubmit="return confirm('Cancel this appointment?');">@csrf @method('PATCH')<input type="hidden" name="status" value="CANCELLED"><button class="inline-flex min-h-10 items-center gap-1.5 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-xs font-extrabold text-red-700 hover:bg-red-600 hover:text-white"><i data-lucide="x" class="h-4 w-4"></i> Cancel</button></form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-14 text-center font-semibold text-gray-500"><i data-lucide="calendar-x" class="mx-auto mb-3 h-12 w-12 text-gray-400"></i><p>No appointments assigned to you today.</p><a href="{{ route('appointments.index') }}" class="btn btn-primary mt-4 inline-flex text-sm">Open appointment calendar</a></div>
            @endforelse
        </section>

        <aside class="space-y-6">
            <div class="card border-2 border-emerald-200 bg-emerald-50"><h2 class="flex items-center gap-2 font-extrabold text-emerald-900"><i data-lucide="user-round" class="h-5 w-5"></i> My profile</h2><p class="mt-3 font-black text-gray-900">{{ $employee->position }}</p><p class="mt-1 text-sm font-semibold text-gray-700">{{ $employee->phone }}</p><p class="mt-1 text-sm font-semibold text-gray-700">{{ $employee->email }}</p><p class="mt-3 border-t border-emerald-200 pt-3 text-sm font-semibold text-emerald-900">{{ $employee->schedule_notes ?: 'No schedule notes available.' }}</p></div>
            <div class="card"><h2 class="flex items-center gap-2 font-extrabold text-gray-900"><i data-lucide="check-circle-2" class="h-5 w-5 text-emerald-600"></i> Progress today</h2><div class="mt-4 flex items-end justify-between"><span class="text-sm font-bold text-gray-500">Completed visits</span><strong class="text-3xl font-black text-emerald-700">{{ $completedToday }}<span class="text-lg text-gray-400">/{{ $todayAppointments->count() }}</span></strong></div><div class="mt-3 h-3 overflow-hidden rounded-full bg-gray-100"><div class="h-full rounded-full bg-emerald-600 {{ $todayAppointments->count() && $completedToday === $todayAppointments->count() ? 'w-full' : ($completedToday > 0 ? 'w-1/2' : 'w-0') }}"></div></div></div>
        </aside>
    </div>
</div>
@endsection
