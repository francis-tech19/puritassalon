@extends(auth()->check() && auth()->user()->isCustomer() ? 'layouts.customer' : 'layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">System Alerts & Notifications</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Stay updated on inventory restock alerts, client loyalty milestones, and appointment reminders.</p>
        </div>
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button type="submit" class="btn btn-secondary text-sm">
                <i data-lucide="check-check" class="w-4 h-4"></i> Mark All as Read
            </button>
        </form>
    </div>

    <!-- Notifications List -->
    <div class="card p-0 overflow-hidden">
        <ul class="divide-y divide-gray-200">
            @forelse($notifications as $notif)
                <li class="p-5 flex items-start justify-between gap-4 hover:bg-pink-50/40 transition {{ $notif->is_read ? 'bg-white' : 'bg-pink-50/20' }}">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0
                            {{ $notif->type === 'INVENTORY' ? 'bg-amber-100 text-amber-800' : ($notif->type === 'LOYALTY' ? 'bg-purple-100 text-purple-800' : 'bg-pink-100 text-[#7A1C49]') }}">
                            @if($notif->type === 'INVENTORY')
                                <i data-lucide="package-x" class="w-5 h-5"></i>
                            @elseif($notif->type === 'LOYALTY')
                                <i data-lucide="award" class="w-5 h-5"></i>
                            @else
                                <i data-lucide="bell" class="w-5 h-5"></i>
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-extrabold text-base text-gray-900">{{ $notif->title }}</h4>
                                @if(!$notif->is_read)
                                    <span class="px-2 py-0.5 rounded-full text-xs font-black bg-[#7A1C49] text-white">NEW</span>
                                @endif
                            </div>
                            <p class="text-sm font-semibold text-gray-600 mt-0.5">{{ $notif->message }}</p>
                            @if($notif->appointment)
                                <div class="mt-2 space-y-1 text-xs text-gray-600">
                                    <div>Customer: <strong>{{ $notif->appointment->customer->full_name ?? 'Customer' }}</strong></div>
                                    <div>Service: {{ $notif->appointment->services->pluck('service_name')->join(', ') }}</div>
                                    <div>Employee: {{ $notif->appointment->employee->full_name ?? 'Unassigned' }}</div>
                                    <div>Appointment: {{ $notif->appointment->appointment_date }} · {{ date('g:i A', strtotime($notif->appointment->start_time)) }}–{{ date('g:i A', strtotime($notif->appointment->end_time)) }}</div>
                                    @if((auth()->user()->isOwnerOrAdmin() || (auth()->user()->isStaff() && auth()->user()->employee_id === $notif->appointment->employee_id)) && $notif->appointment->customer?->phone)
                                        <a class="font-bold text-[#7A1C49] underline" href="tel:{{ preg_replace('/[^0-9+]/', '', $notif->appointment->customer->phone) }}">Call {{ $notif->appointment->customer->full_name }}</a>
                                    @endif
                                </div>
                                <a href="{{ auth()->user()->isCustomer() ? route('customer.dashboard', ['view' => 'appointments']) : route('appointments.index', ['date' => $notif->appointment->appointment_date]) }}" class="mt-2 inline-block text-xs font-bold text-[#7A1C49] underline">View Appointment</a>
                            @endif
                            <span class="text-xs text-gray-400 font-bold mt-1 block">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if(!$notif->is_read)
                        <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl border border-gray-300 hover:bg-gray-100 text-xs font-bold text-gray-700 transition shrink-0">
                                Mark Read
                            </button>
                        </form>
                    @endif
                </li>
            @empty
                <li class="py-16 text-center text-gray-400 font-bold">
                    <i data-lucide="bell-off" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                    No notifications in your inbox.
                </li>
            @endforelse
        </ul>

        <div class="p-4 border-t">
            {{ $notifications->links() }}
        </div>
    </div>

</div>
@endsection
