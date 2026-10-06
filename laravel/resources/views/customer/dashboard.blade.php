@extends('layouts.customer')

@section('content')
<div class="space-y-8" x-data="{ 
    customerView: @js(request('view', 'home')),
    activeServiceCategory: 'All',
    serviceSearchQuery: '',
    appointmentFilter: 'All',
    selectedServiceForBooking: @js($selectedServiceId ?? null),
    bookingStep: 1,
    rescheduleModalOpen: false,
    activeAppointmentForReschedule: null,
    detailsModalOpen: false,
    activeAppointmentForDetails: null,
    selectedEmployeeForBooking: '',
    selectedServiceName: '',
    selectedServiceDuration: 0,
    selectedEmployeeName: '',
    selectedAppointmentDate: @js(now()->toDateString()),
    selectedStartTime: '',
    bookingTimeSlots: [],
    availabilityLoading: false,
    availabilityError: '',
    async loadBookingSlots() {
        this.bookingTimeSlots = [];
        this.selectedStartTime = '';
        this.availabilityError = '';
        if (!this.selectedServiceForBooking || !this.selectedEmployeeForBooking || !this.selectedAppointmentDate) return;
        this.availabilityLoading = true;
        try {
            const params = new URLSearchParams({
                service_id: this.selectedServiceForBooking,
                employee_id: this.selectedEmployeeForBooking,
                appointment_date: this.selectedAppointmentDate
            });
            const response = await fetch('{{ route('customer.appointments.availability') }}?' + params.toString(), {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Unable to load available times.');
            this.bookingTimeSlots = result.slots;
        } catch (error) {
            this.availabilityError = error.message;
        } finally {
            this.availabilityLoading = false;
        }
    }
}">

    @php
        // Helper function for service fallback photos
        $getServiceImage = function($service) {
            if ($service->photo_path) {
                return asset('storage/' . $service->photo_path);
            }
            $map = [
                'Haircut & Blowdry (Women)' => 'haircut.png',
                'Haircut (Men)' => 'haircut.png',
                'Full Hair Coloring' => 'hair-coloring.png',
                'Keratin Hair Rebonding' => 'rebonding.png',
                'Classic Manicure' => 'manicure.png',
                'Classic Pedicure' => 'pedicure.png',
                'Gel Manicure & Pedicure Combo' => 'manicure.png',
                'Facial Deep Cleansing' => 'facial.png',
            ];
            $fileName = $map[$service->service_name] ?? null;
            if ($fileName) {
                return asset('images/' . $fileName);
            }
            // Fallback to category-based image
            $cat = strtolower($service->category ?? '');
            if (str_contains($cat, 'hair')) return asset('images/haircut.png');
            if (str_contains($cat, 'nail')) return asset('images/manicure.png');
            if (str_contains($cat, 'facial') || str_contains($cat, 'skin')) return asset('images/facial.png');
            return asset('images/haircut.png');
        };

        $nextAppointment = $upcomingAppointments->first();
    @endphp

    {{-- ====================================================================== --}}
    {{-- VIEW 1: HOME (CUSTOMER DASHBOARD)                                      --}}
    {{-- ====================================================================== --}}
    <div x-show="customerView === 'home'" x-cloak class="space-y-7">

        {{-- 1. Welcome Banner Card --}}
        <section class="rounded-3xl bg-gradient-to-r from-[#5C1236] via-[#7A1C49] to-[#3D0B24] text-white p-6 sm:p-8 shadow-md relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <p class="text-xs sm:text-sm font-bold uppercase tracking-wider text-pink-200">Welcome back,</p>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold font-display tracking-tight text-white leading-tight">
                        {{ $customer->full_name ?? auth()->user()->name ?? 'Isabella Lim' }}
                    </h1>
                    
                    <div class="flex items-center gap-2 pt-1 text-sm font-bold text-[#F59E0B]">
                        <i data-lucide="trophy" class="w-4 h-4 text-[#F59E0B] shrink-0"></i>
                        <span>{{ $loyaltyTier ?? 'Gold' }} Member &bull; {{ number_format($loyaltyPoints ?? 2450) }} pts</span>
                    </div>

                    {{-- Profile Completion Bar --}}
                    <div class="pt-3 max-w-lg">
                        <div class="flex items-center justify-between text-xs font-semibold text-pink-200 mb-1.5">
                            <span>Profile Completion</span>
                            <span>{{ $profileCompletion ?? 85 }}%</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-[#3D0B24]/80 p-0.5 border border-pink-400/20">
                               <div x-data="{ completion: @js($profileCompletion ?? 85) }"
                                   :style="'width: ' + completion + '%'"
                                   class="h-full rounded-full bg-gradient-to-r from-[#D97706] to-[#F59E0B] transition-all duration-500"></div>
                        </div>
                    </div>
                </div>

                {{-- Right Avatar Circle --}}
                <div class="shrink-0 self-start md:self-center">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-[#3D0B24] border-2 border-pink-300/30 text-white font-black text-2xl sm:text-3xl flex items-center justify-center shadow-lg font-display">
                        {{ $initials ?? 'IL' }}
                    </div>
                </div>
            </div>

            {{-- Subtle Decorative Pattern --}}
            <div class="absolute -right-16 -bottom-16 w-64 h-64 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
        </section>

        {{-- Selected Service Notice --}}
        @if($selectedServiceId)
            <div class="rounded-2xl border-2 border-amber-200 bg-amber-50 p-5 text-sm font-bold text-amber-900 flex items-center justify-between gap-4 shadow-xs" role="status">
                <div class="flex items-center gap-3">
                    <i data-lucide="sparkles" class="w-6 h-6 text-[#D97706] shrink-0"></i>
                    <div>
                        <p class="font-extrabold text-base">Your service choice is saved!</p>
                        <p class="text-xs font-semibold text-amber-800">Complete your booking by selecting a stylist, date, and preferred time.</p>
                    </div>
                </div>
                <button @click="customerView = 'booking'" class="btn bg-[#7A1C49] hover:bg-[#5C1236] text-white px-4 py-2 rounded-xl text-xs font-bold shrink-0">
                    Continue Booking
                </button>
            </div>
        @endif

        {{-- 2. 4 Stat KPI Cards --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5" aria-label="Customer statistics">
            {{-- Loyalty Points --}}
            <div class="bg-white rounded-2xl border border-gray-200/90 p-5 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Loyalty Points</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-gray-900 mt-1 leading-none">{{ number_format($loyaltyPoints ?? 2450) }}</h3>
                    <span class="text-xs font-semibold text-gray-400 mt-1.5 block">{{ $loyaltyTier ?? 'Gold' }}</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#FDF2F7] flex items-center justify-center text-[#7A1C49] shrink-0">
                    <i data-lucide="award" class="w-6 h-6"></i>
                </div>
            </div>

            {{-- Total Visits --}}
            <div class="bg-white rounded-2xl border border-gray-200/90 p-5 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Visits</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-gray-900 mt-1 leading-none">{{ $customer->visit_count ?? 0 }}</h3>
                    <span class="text-xs font-semibold text-gray-400 mt-1.5 block">All time</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#FFF7ED] flex items-center justify-center text-[#D97706] shrink-0">
                    <i data-lucide="scissors" class="w-6 h-6"></i>
                </div>
            </div>

            {{-- Appointments --}}
            <div class="bg-white rounded-2xl border border-gray-200/90 p-5 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Appointments</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-gray-900 mt-1 leading-none">{{ $appointments->count() }}</h3>
                    <span class="text-xs font-semibold text-gray-400 mt-1.5 block">This account</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                    <i data-lucide="calendar" class="w-6 h-6"></i>
                </div>
            </div>

            {{-- Completed --}}
            <div class="bg-white rounded-2xl border border-gray-200/90 p-5 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Completed</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-gray-900 mt-1 leading-none">{{ $completedAppointments->count() }}</h3>
                    <span class="text-xs font-semibold text-gray-400 mt-1.5 block">Services received</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
            </div>
        </section>

        {{-- 3. Upcoming Appointment Card --}}
        <section class="bg-white rounded-3xl border border-gray-200/90 p-6 sm:p-7 shadow-xs">
            <div class="flex items-center justify-between gap-3 mb-5 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="calendar" class="w-6 h-6 text-blue-600"></i>
                    <h2 class="text-xl font-black text-gray-900">Upcoming Appointment</h2>
                </div>

                @if($nextAppointment)
                    @if($nextAppointment->status === 'PENDING')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]">
                            Pending Approval
                        </span>
                    @elseif($nextAppointment->status === 'CONFIRMED')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                            Confirmed
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                            {{ $nextAppointment->status }}
                        </span>
                    @endif
                @endif
            </div>

            @if($nextAppointment)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-6">
                    <div>
                        <span class="text-xs font-bold uppercase text-gray-400 block mb-1">Service</span>
                        <p class="text-base font-black text-gray-900">
                            {{ $nextAppointment->services->pluck('service_name')->join(', ') }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs font-bold uppercase text-gray-400 block mb-1">With</span>
                        <p class="text-base font-black text-gray-900">
                            {{ $nextAppointment->employee->full_name ?? 'Any Available Stylist' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs font-bold uppercase text-gray-400 block mb-1">Date</span>
                        <p class="text-base font-black text-gray-900">
                            {{ \Carbon\Carbon::parse($nextAppointment->appointment_date)->format('Y-m-d') }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs font-bold uppercase text-gray-400 block mb-1">Time</span>
                        <p class="text-base font-black text-gray-900">
                            {{ \Carbon\Carbon::parse($nextAppointment->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($nextAppointment->end_time)->format('g:i A') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button @click="customerView = 'appointments'" 
                            class="px-5 py-2.5 rounded-xl border-2 border-[#7A1C49] text-[#7A1C49] hover:bg-[#FDF2F7] font-bold text-sm transition">
                        View Details
                    </button>

                    @if(in_array($nextAppointment->status, ['PENDING', 'CONFIRMED'], true))
                        <button @click="activeAppointmentForReschedule = @js($nextAppointment); rescheduleModalOpen = true"
                                class="px-5 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-100 font-bold text-sm transition">
                            Reschedule
                        </button>
                    @endif

                    <div class="ml-auto">
                        @include('appointments._proof', ['appointment' => $nextAppointment])
                    </div>
                </div>
            @else
                <div class="py-8 text-center bg-gray-50/70 rounded-2xl border border-dashed border-gray-200">
                    <div class="w-12 h-12 rounded-2xl bg-pink-100 text-[#7A1C49] flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="calendar-plus" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">No upcoming appointment scheduled</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Book your hair styling, manicure, or facial with our professional salon specialists.</p>
                    <button @click="customerView = 'booking'" class="mt-4 px-5 py-2.5 rounded-xl bg-[#7A1C49] hover:bg-[#5C1236] text-white font-bold text-sm transition shadow-sm">
                        Book an Appointment
                    </button>
                </div>
            @endif
        </section>

        {{-- 4. Quick Actions --}}
        <section>
            <h2 class="text-xl font-black text-gray-900 mb-4 tracking-tight">Quick Actions</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
                {{-- Book Appointment --}}
                <button @click="customerView = 'booking'" 
                        class="bg-white rounded-2xl border border-gray-200/90 p-4 flex flex-col items-center justify-center text-center gap-2.5 hover:border-[#7A1C49]/40 hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-105 transition">
                        <i data-lucide="calendar" class="w-6 h-6"></i>
                    </div>
                    <span class="text-sm font-bold text-gray-800">Book Appointment</span>
                </button>

                {{-- My Appointments --}}
                <button @click="customerView = 'appointments'" 
                        class="bg-white rounded-2xl border border-gray-200/90 p-4 flex flex-col items-center justify-center text-center gap-2.5 hover:border-[#7A1C49]/40 hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center group-hover:scale-105 transition">
                        <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                    </div>
                    <span class="text-sm font-bold text-gray-800">My Appointments</span>
                </button>

                {{-- View Services --}}
                <button @click="customerView = 'services'" 
                        class="bg-white rounded-2xl border border-gray-200/90 p-4 flex flex-col items-center justify-center text-center gap-2.5 hover:border-[#7A1C49]/40 hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-2xl bg-pink-50 text-[#7A1C49] flex items-center justify-center group-hover:scale-105 transition">
                        <i data-lucide="sparkles" class="w-6 h-6"></i>
                    </div>
                    <span class="text-sm font-bold text-gray-800">View Services</span>
                </button>

                {{-- Loyalty Rewards --}}
                <button @click="customerView = 'loyalty'" 
                        class="bg-white rounded-2xl border border-gray-200/90 p-4 flex flex-col items-center justify-center text-center gap-2.5 hover:border-[#7A1C49]/40 hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#D97706] flex items-center justify-center group-hover:scale-105 transition">
                        <i data-lucide="gift" class="w-6 h-6"></i>
                    </div>
                    <span class="text-sm font-bold text-gray-800">Loyalty Rewards</span>
                </button>

                {{-- Notifications --}}
                <button @click="customerView = 'notifications'" 
                        class="bg-white rounded-2xl border border-gray-200/90 p-4 flex flex-col items-center justify-center text-center gap-2.5 hover:border-[#7A1C49]/40 hover:shadow-md transition group col-span-2 sm:col-span-1">
                    <div class="w-12 h-12 rounded-2xl bg-yellow-50 text-yellow-600 flex items-center justify-center group-hover:scale-105 transition relative">
                        <i data-lucide="bell" class="w-6 h-6"></i>
                        @if(!empty($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                            <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-[#D97706] text-white text-[10px] font-black flex items-center justify-center">
                                {{ $unreadNotificationsCount }}
                            </span>
                        @endif
                    </div>
                    <span class="text-sm font-bold text-gray-800">Notifications</span>
                </button>
            </div>
        </section>

        {{-- 5. Recommended for You (RETAINING SERVICE IMAGES) --}}
        <section>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Recommended for You</h2>
                <button @click="customerView = 'services'" class="text-sm font-bold text-[#7A1C49] hover:underline flex items-center gap-1">
                    See all services <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($services->take(3) as $recService)
                    @php($imgUrl = $getServiceImage($recService))
                    <article class="bg-white rounded-3xl border border-gray-200/90 p-5 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            {{-- Retained Service Photo with rounded corners --}}
                            <div class="relative w-full h-44 rounded-2xl overflow-hidden mb-4 bg-pink-50 border border-gray-100">
                                <img src="{{ $imgUrl }}" alt="{{ $recService->service_name }}" class="w-full h-full object-cover">
                                <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold bg-white/95 text-[#7A1C49] shadow-xs backdrop-blur-xs">
                                    {{ $recService->category ?? 'Hair' }}
                                </span>
                            </div>

                            <div class="flex items-baseline justify-between gap-2">
                                <h3 class="text-lg font-black text-gray-900 leading-tight">{{ $recService->service_name }}</h3>
                                <strong class="text-xl font-black text-gray-900 shrink-0">₱{{ number_format($recService->price, 0) }}</strong>
                            </div>

                            <p class="text-xs font-semibold text-gray-500 mt-1">{{ $recService->duration_minutes }} min</p>

                            {{-- 5 Gold Rating Stars --}}
                            <div class="flex items-center gap-1 text-[#D97706] mt-2">
                                @for($star = 1; $star <= 5; $star++)
                                    <i data-lucide="star" class="w-3.5 h-3.5 fill-[#D97706]"></i>
                                @endfor
                            </div>
                        </div>

                        <div class="pt-5 mt-auto">
                            <button @click="selectedServiceForBooking = {{ $recService->id }}; selectedServiceName = @js($recService->service_name); selectedServiceDuration = {{ $recService->duration_minutes }}; selectedEmployeeForBooking = ''; selectedEmployeeName = ''; selectedStartTime = ''; bookingTimeSlots = []; customerView = 'booking'; bookingStep = 2"
                                    class="w-full py-3 px-4 rounded-xl bg-[#7A1C49] hover:bg-[#5C1236] text-white font-bold text-sm transition text-center shadow-xs flex items-center justify-center gap-2">
                                <span>Book</span>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>


    {{-- ====================================================================== --}}
    {{-- VIEW 2: BOOK AN APPOINTMENT (Figma Stepper & Flow)                     --}}
    {{-- ====================================================================== --}}
    <div x-show="customerView === 'booking'" x-cloak class="space-y-7">
        {{-- Header & Stepper --}}
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Book an Appointment</h1>
            <p class="text-sm font-semibold text-gray-500 mt-0.5">Choose a service, employee, date, and currently available time.</p>
        </div>

        {{-- Stepper Component (1 Service, 2 Employee, 3 Date, 4 Time, 5 Review) --}}
        <div class="bg-white rounded-2xl border border-gray-200/90 p-4 sm:p-6 shadow-xs">
            <div class="flex items-center justify-between max-w-2xl mx-auto relative">
                <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-gray-200 -translate-y-1/2 z-0"></div>

                {{-- Step 1: Service --}}
                <div class="relative z-10 flex flex-col items-center gap-1.5 cursor-pointer" @click="bookingStep = 1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition"
                         :class="bookingStep >= 1 ? 'bg-[#7A1C49] text-white' : 'bg-gray-100 text-gray-500'">1</div>
                    <span class="text-xs font-bold" :class="bookingStep === 1 ? 'text-[#7A1C49]' : 'text-gray-500'">Service</span>
                </div>

                {{-- Step 2: Employee --}}
                <div class="relative z-10 flex flex-col items-center gap-1.5 cursor-pointer" @click="if(selectedServiceForBooking) bookingStep = 2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition"
                         :class="bookingStep >= 2 ? 'bg-[#7A1C49] text-white' : 'bg-gray-100 text-gray-500'">2</div>
                    <span class="text-xs font-bold" :class="bookingStep === 2 ? 'text-[#7A1C49]' : 'text-gray-500'">Employee</span>
                </div>

                {{-- Step 3: Date --}}
                <div class="relative z-10 flex flex-col items-center gap-1.5 cursor-pointer" @click="if(selectedServiceForBooking) bookingStep = 3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition"
                         :class="bookingStep >= 3 ? 'bg-[#7A1C49] text-white' : 'bg-gray-100 text-gray-500'">3</div>
                    <span class="text-xs font-bold" :class="bookingStep === 3 ? 'text-[#7A1C49]' : 'text-gray-500'">Date</span>
                </div>

                {{-- Step 4: Time --}}
                <div class="relative z-10 flex flex-col items-center gap-1.5 cursor-pointer" @click="if(selectedServiceForBooking) bookingStep = 4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition"
                         :class="bookingStep >= 4 ? 'bg-[#7A1C49] text-white' : 'bg-gray-100 text-gray-500'">4</div>
                    <span class="text-xs font-bold" :class="bookingStep === 4 ? 'text-[#7A1C49]' : 'text-gray-500'">Time</span>
                </div>

                {{-- Step 5: Review --}}
                <div class="relative z-10 flex flex-col items-center gap-1.5 cursor-pointer" @click="if(selectedServiceForBooking) bookingStep = 5">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition"
                         :class="bookingStep >= 5 ? 'bg-[#7A1C49] text-white' : 'bg-gray-100 text-gray-500'">5</div>
                    <span class="text-xs font-bold" :class="bookingStep === 5 ? 'text-[#7A1C49]' : 'text-gray-500'">Review</span>
                </div>
            </div>
        </div>

        {{-- Booking Form Container --}}
        <form method="POST" action="{{ route('customer.appointments.book') }}" id="customer-booking-form">
            @csrf
            
            {{-- STEP 1: Select a Service (With Service Images retained) --}}
            <div x-show="bookingStep === 1" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Available services</h2>
                    <span class="text-xs font-semibold text-gray-500">{{ $services->count() }} services available</span>
                </div>

                <div class="space-y-3">
                    @foreach($services as $srv)
                        @php($srvImg = $getServiceImage($srv))
                        <div class="bg-white rounded-2xl border-2 p-4 sm:p-5 transition cursor-pointer flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                             :class="selectedServiceForBooking == {{ $srv->id }} ? 'border-[#7A1C49] bg-pink-50/20 ring-2 ring-[#7A1C49]/20' : 'border-gray-200/80 hover:border-gray-300'"
                             @click="selectedServiceForBooking = {{ $srv->id }}; selectedServiceName = @js($srv->service_name); selectedServiceDuration = {{ $srv->duration_minutes }}; selectedEmployeeForBooking = ''; selectedEmployeeName = ''; selectedStartTime = ''; bookingTimeSlots = []; bookingStep = 2">
                            
                            <div class="flex items-center gap-4 min-w-0">
                                {{-- Service Image --}}
                                <img src="{{ $srvImg }}" alt="{{ $srv->service_name }}" class="w-20 h-20 rounded-xl object-cover shrink-0 border border-gray-100">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base font-black text-gray-900 leading-snug">{{ $srv->service_name }}</h3>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 max-w-xl">{{ $srv->description ?: 'Professional salon care tailored to your preference.' }}</p>
                                    <div class="flex items-center gap-3 mt-2 text-xs font-semibold text-gray-500">
                                        <span><i data-lucide="clock" class="w-3.5 h-3.5 inline text-gray-400"></i> {{ $srv->duration_minutes }} min</span>
                                        <div class="flex text-[#D97706] text-xs">★★★★★</div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100">
                                <span class="text-xl font-black text-gray-900">₱{{ number_format($srv->price, 0) }}</span>
                                <button type="button" class="btn px-4 py-2 text-xs font-bold rounded-xl mt-2"
                                        :class="selectedServiceForBooking == {{ $srv->id }} ? 'bg-[#7A1C49] text-white' : 'border border-gray-300 text-gray-700'">
                                    <span x-text="selectedServiceForBooking == {{ $srv->id }} ? 'Selected' : 'Select'"></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Hidden input for service_id --}}
            <input type="hidden" name="service_id" :value="selectedServiceForBooking" required>

            {{-- STEP 2 to 5: Stylist, Date, Time & Final Review in Clean Wizard Card --}}
            <div x-show="bookingStep >= 2" class="bg-white rounded-3xl border border-gray-200/90 p-6 sm:p-8 shadow-xs space-y-6">
                
                {{-- Selected service summary chip --}}
                <div class="p-4 rounded-2xl bg-pink-50 border border-pink-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i data-lucide="sparkles" class="w-5 h-5 text-[#7A1C49]"></i>
                        <span class="text-sm font-bold text-[#7A1C49]">Configuring appointment details</span>
                    </div>
                    <button type="button" @click="bookingStep = 1" class="text-xs font-bold text-[#7A1C49] underline">
                        Change Service
                    </button>
                </div>

                {{-- Step 2: Stylist --}}
                <div x-show="bookingStep === 2" class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-900">Select an Employee / Stylist</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($employees as $employee)
                            <label class="p-4 rounded-2xl border-2 flex items-center gap-4 cursor-pointer transition hover:border-[#7A1C49]"
                                   :class="$el.querySelector('input').checked ? 'border-[#7A1C49] bg-pink-50/30' : 'border-gray-200'">
                                <input type="radio" name="employee_id" value="{{ $employee->id }}" x-model="selectedEmployeeForBooking" @change="selectedEmployeeName = @js($employee->full_name); loadBookingSlots()" class="text-[#7A1C49] focus:ring-[#D97706] w-4 h-4">
                                <div>
                                    <h4 class="font-black text-gray-900">{{ $employee->full_name }}</h4>
                                    <p class="text-xs text-gray-500 font-semibold">{{ $employee->position ?? 'Professional Stylist' }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" @click="bookingStep = 1" class="btn btn-secondary text-sm">Back</button>
                        <button type="button" @click="if(selectedEmployeeForBooking) bookingStep = 3" :disabled="!selectedEmployeeForBooking" class="btn btn-primary text-sm disabled:opacity-50">Next: Choose Date</button>
                    </div>
                </div>

                {{-- Step 3: Date --}}
                <div x-show="bookingStep === 3" class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-900">Select Date</h2>
                    <div class="max-w-md">
                        <label class="block text-xs font-black uppercase text-gray-600 mb-1">Appointment Date</label>
                        <input type="date" name="appointment_date" min="{{ now()->toDateString() }}" x-model="selectedAppointmentDate" @change="loadBookingSlots()" required class="form-input text-base">
                        <p class="text-xs font-semibold text-gray-500 mt-2">Weekdays: {{ date('g:i A', strtotime($businessSettings?->opening_time ?? '10:00')) }}–{{ date('g:i A', strtotime($businessSettings?->closing_time ?? '16:00')) }}. Weekends: {{ date('g:i A', strtotime($businessSettings?->weekend_opening_time ?? '09:00')) }}–{{ date('g:i A', strtotime($businessSettings?->weekend_closing_time ?? '17:00')) }}.</p>
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" @click="bookingStep = 2" class="btn btn-secondary text-sm">Back</button>
                        <button type="button" @click="if(selectedAppointmentDate) { bookingStep = 4; loadBookingSlots() }" :disabled="!selectedAppointmentDate" class="btn btn-primary text-sm disabled:opacity-50">Next: Choose Time</button>
                    </div>
                </div>

                {{-- Step 4: Time --}}
                <div x-show="bookingStep === 4" class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-900">Select Time Slot</h2>
                    <div class="max-w-md">
                        <label class="block text-xs font-black uppercase text-gray-600 mb-1">Available Start Times</label>
                        <input type="hidden" name="start_time" :value="selectedStartTime">
                        <div x-show="availabilityLoading" class="text-sm text-gray-500" role="status">Checking employee availability...</div>
                        <div x-show="availabilityError" x-text="availabilityError" class="text-sm font-semibold text-red-700" role="alert"></div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2" role="group" aria-label="Appointment time slots">
                            <template x-for="slot in bookingTimeSlots" :key="slot.start_time">
                                <button type="button" @click="if(slot.available) selectedStartTime = slot.start_time"
                                        :disabled="!slot.available"
                                        :aria-label="slot.start_time + (slot.available ? ' available' : ' unavailable')"
                                        :class="!slot.available ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : (selectedStartTime === slot.start_time ? 'bg-[#7A1C49] text-white border-[#7A1C49]' : 'bg-white text-gray-800 border-gray-300 hover:border-[#7A1C49]')"
                                        class="rounded-lg border px-3 py-2 text-sm font-bold text-left">
                                    <span x-text="new Date('2000-01-01T' + slot.start_time + ':00').toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })"></span>
                                    <span x-show="!slot.available" class="block text-xs">Unavailable</span>
                                    <span x-show="slot.available" class="block text-xs opacity-80" x-text="'Until ' + slot.end_time"></span>
                                </button>
                            </template>
                        </div>
                        <p x-show="!availabilityLoading && bookingTimeSlots.length === 0 && selectedEmployeeForBooking" class="text-sm text-gray-500">No time slots fit this service and employee's schedule.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-900 flex items-start gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-[#D97706] shrink-0 mt-0.5"></i>
                        <span>Only times that fit the service duration, salon hours, employee schedule, and existing appointments can be selected.</span>
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" @click="bookingStep = 3" class="btn btn-secondary text-sm">Back</button>
                        <button type="button" @click="if(selectedStartTime) bookingStep = 5" :disabled="!selectedStartTime" class="btn btn-primary text-sm disabled:opacity-50">Next: Review & Submit</button>
                    </div>
                </div>

                {{-- Step 5: Review & Submit --}}
                <div x-show="bookingStep === 5" class="space-y-5">
                    <h2 class="text-xl font-bold text-gray-900">Review Appointment Request</h2>
                    <div class="rounded-2xl bg-gray-50 border border-gray-200 p-5 space-y-3">
                        <div class="flex justify-between text-sm"><span class="text-gray-500 font-semibold">Client Name:</span><strong class="text-gray-900">{{ $customer->full_name ?? auth()->user()->name }}</strong></div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500 font-semibold">Service:</span><strong class="text-gray-900" x-text="selectedServiceName + ' (' + selectedServiceDuration + ' min)' "></strong></div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500 font-semibold">Employee:</span><strong class="text-gray-900" x-text="selectedEmployeeName"></strong></div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500 font-semibold">Date & time:</span><strong class="text-gray-900" x-text="selectedAppointmentDate + ' · ' + selectedStartTime + '–' + (bookingTimeSlots.find(slot => slot.start_time === selectedStartTime)?.end_time || '')"></strong></div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500 font-semibold">Status:</span><span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Confirmed automatically</span></div>
                        <div class="flex justify-between text-sm border-t pt-3"><span class="text-gray-500 font-semibold">Payment upon visit:</span><strong class="text-[#7A1C49] font-black">Cash, GCash, or Card accepted at front desk</strong></div>
                    </div>

                    <div class="flex justify-between pt-4">
                        <button type="button" @click="bookingStep = 4" class="btn btn-secondary text-sm">Back</button>
                        <button type="submit" :disabled="!selectedStartTime" class="btn btn-primary text-base px-8 py-3 font-bold shadow-md disabled:opacity-50">
                            <i data-lucide="calendar-check" class="w-5 h-5"></i> Submit Request
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>


    {{-- ====================================================================== --}}
    {{-- VIEW 3: MY APPOINTMENTS (Figma Filter Pills & Cards)                   --}}
    {{-- ====================================================================== --}}
    <div x-show="customerView === 'appointments'" x-cloak class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">My Appointments</h1>
            <p class="text-sm font-semibold text-gray-500 mt-0.5">Upcoming appointments and past salon history.</p>
        </div>

        {{-- Filter Pills matching Figma: All, Pending Approval, Confirmed, Completed, Cancelled, Rejected --}}
        <div class="flex flex-wrap gap-2 pt-1">
            <button type="button" @click="appointmentFilter = 'All'" 
                    :class="appointmentFilter === 'All' ? 'bg-[#7A1C49] text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'" 
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition">
                All
            </button>
            <button type="button" @click="appointmentFilter = 'PENDING'" 
                    :class="appointmentFilter === 'PENDING' ? 'bg-[#7A1C49] text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'" 
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition">
                Pending Approval
            </button>
            <button type="button" @click="appointmentFilter = 'CONFIRMED'" 
                    :class="appointmentFilter === 'CONFIRMED' ? 'bg-[#7A1C49] text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'" 
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition">
                Confirmed
            </button>
            <button type="button" @click="appointmentFilter = 'COMPLETED'" 
                    :class="appointmentFilter === 'COMPLETED' ? 'bg-[#7A1C49] text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'" 
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition">
                Completed
            </button>
            <button type="button" @click="appointmentFilter = 'CANCELLED'" 
                    :class="appointmentFilter === 'CANCELLED' ? 'bg-[#7A1C49] text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'" 
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition">
                Cancelled
            </button>
        </div>

        {{-- Appointment Cards --}}
        <div class="space-y-4">
            @forelse($appointments as $appt)
                <div x-show="appointmentFilter === 'All' || appointmentFilter === '{{ $appt->status }}'"
                     class="bg-white rounded-3xl border border-gray-200/90 p-5 sm:p-6 shadow-xs grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_minmax(340px,0.85fr)] items-start gap-5">
                    
                    <div class="min-w-0 space-y-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-lg font-black text-gray-900">
                                {{ $appt->services->pluck('service_name')->join(', ') }}
                            </h3>
                            @if($appt->status === 'PENDING')
                                <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]">
                                    Pending Approval
                                </span>
                            @elseif($appt->status === 'CONFIRMED')
                                <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    Confirmed
                                </span>
                            @elseif($appt->status === 'COMPLETED')
                                <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Completed
                                </span>
                            @elseif($appt->status === 'CANCELLED')
                                <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                    Cancelled
                                </span>
                            @else
                                <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                    {{ $appt->status }}
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-semibold text-gray-600">
                            <div><span class="text-gray-400 block">Employee:</span> <strong class="text-gray-900">{{ $appt->employee->full_name ?? 'Salon Team' }}</strong></div>
                            <div><span class="text-gray-400 block">Date:</span> <strong class="text-gray-900">{{ $appt->appointment_date }}</strong></div>
                            <div><span class="text-gray-400 block">Time:</span> <strong class="text-gray-900">{{ \Carbon\Carbon::parse($appt->start_time)->format('g:i A') }}</strong></div>
                            <div><span class="text-gray-400 block">Price:</span> <strong class="text-[#7A1C49] font-black">₱{{ number_format($appt->total_amount, 0) }}</strong></div>
                        </div>

                        @if($appt->notes)
                            <p class="text-xs text-gray-500 italic mt-1">&ldquo;{{ $appt->notes }}&rdquo;</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-wrap items-center gap-2.5">
                        @if(in_array($appt->status, ['PENDING', 'CONFIRMED'], true))
                            <button @click="activeAppointmentForReschedule = @js($appt); rescheduleModalOpen = true" 
                                    class="px-4 py-2 rounded-xl border-2 border-gray-300 hover:bg-gray-100 text-xs font-bold text-gray-700 transition">
                                Reschedule
                            </button>

                            <form method="POST" action="{{ route('customer.appointments.cancel', $appt) }}" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition shadow-xs">
                                    Cancel
                                </button>
                            </form>
                        @endif

                        @if($appt->status === 'COMPLETED')
                            <button @click="customerView = 'feedback'" 
                                    class="px-4 py-2 rounded-xl bg-[#D97706] hover:bg-[#B45309] text-white text-xs font-bold transition shadow-xs">
                                Rate Service
                            </button>
                        @endif
                    </div>

                    <div class="w-full max-w-xl xl:justify-self-end">
                        @include('appointments._proof', ['appointment' => $appt])
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-gray-200/90 p-12 text-center">
                    <i data-lucide="calendar-x" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                    <h3 class="text-lg font-bold text-gray-900">No appointments found</h3>
                    <p class="text-xs text-gray-500 mt-1">You do not have any recorded appointments matching this view.</p>
                </div>
            @endforelse
        </div>
    </div>


    {{-- ====================================================================== --}}
    {{-- VIEW 4: OUR SERVICES (Figma Search, Filter Pills & Service Images)     --}}
    {{-- ====================================================================== --}}
    <div x-show="customerView === 'services'" x-cloak class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Our Services</h1>
            <p class="text-sm font-semibold text-gray-500 mt-0.5">Explore our wide selection of treatments, styling, and beauty experiences.</p>
        </div>

        {{-- Search & Category Filter Bar matching Figma --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" x-model="serviceSearchQuery" placeholder="Search services..." 
                       class="form-input pl-10 text-sm rounded-2xl bg-white">
            </div>

            <div class="flex flex-wrap gap-2">
                <template x-for="cat in ['All', 'Hair', 'Nails', 'Skin', 'Beauty']" :key="cat">
                    <button type="button" @click="activeServiceCategory = cat" 
                            :class="activeServiceCategory === cat ? 'bg-[#7A1C49] text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'"
                            class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition" 
                            x-text="cat">
                    </button>
                </template>
            </div>
        </div>

        {{-- Services Grid (3x3 matching Figma, WITH SERVICE IMAGES RETAINED) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($services as $srv)
                @php($srvImg = $getServiceImage($srv))
                <article class="bg-white rounded-3xl border border-gray-200/90 p-5 shadow-xs hover:shadow-md transition flex flex-col justify-between"
                         x-show="(activeServiceCategory === 'All' || '{{ strtolower($srv->category) }}'.includes(activeServiceCategory.toLowerCase())) && 
                                 (!serviceSearchQuery || '{{ strtolower($srv->service_name) }}'.includes(serviceSearchQuery.toLowerCase()))">
                    <div>
                        {{-- Service Image retained --}}
                        <div class="relative w-full h-44 rounded-2xl overflow-hidden mb-4 bg-pink-50 border border-gray-100">
                            <img src="{{ $srvImg }}" alt="{{ $srv->service_name }}" class="w-full h-full object-cover">
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold bg-white/95 text-[#7A1C49] shadow-xs backdrop-blur-xs">
                                {{ $srv->category ?? 'Hair' }}
                            </span>
                        </div>

                        <div class="flex items-baseline justify-between gap-2">
                            <h3 class="text-lg font-black text-gray-900 leading-tight">{{ $srv->service_name }}</h3>
                            <strong class="text-xl font-black text-gray-900 shrink-0">₱{{ number_format($srv->price, 0) }}</strong>
                        </div>

                        <p class="text-xs text-gray-500 font-medium mt-1.5 line-clamp-2 min-h-[2rem]">
                            {{ $srv->description ?: 'Precision salon styling and care with premium products.' }}
                        </p>

                        <div class="flex items-center justify-between mt-3 text-xs font-semibold text-gray-500">
                            <div class="flex items-center gap-1 text-[#D97706]">
                                @for($star = 1; $star <= 5; $star++)
                                    <i data-lucide="star" class="w-3.5 h-3.5 fill-[#D97706]"></i>
                                @endfor
                                <span class="text-gray-400 ml-1">(4.8)</span>
                            </div>
                            <span><i data-lucide="clock" class="w-3.5 h-3.5 inline text-gray-400"></i> {{ $srv->duration_minutes }} min</span>
                        </div>
                    </div>

                    <div class="pt-5 mt-auto">
                        <button @click="selectedServiceForBooking = {{ $srv->id }}; selectedServiceName = @js($srv->service_name); selectedServiceDuration = {{ $srv->duration_minutes }}; selectedEmployeeForBooking = ''; selectedEmployeeName = ''; selectedStartTime = ''; bookingTimeSlots = []; customerView = 'booking'; bookingStep = 2"
                                class="w-full py-3 px-4 rounded-xl bg-[#7A1C49] hover:bg-[#5C1236] text-white font-bold text-sm transition text-center shadow-xs flex items-center justify-center gap-2">
                            <span>Book This Service</span>
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>


    {{-- ====================================================================== --}}
    {{-- VIEW 5: LOYALTY REWARDS                                                --}}
    {{-- ====================================================================== --}}
    <div x-show="customerView === 'loyalty'" x-cloak class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Loyalty Rewards</h1>
            <p class="text-sm font-semibold text-gray-500 mt-0.5">Earn points on every visit and redeem exclusive salon discounts.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-gradient-to-br from-[#7A1C49] to-[#3D0B24] rounded-3xl text-white p-6 shadow-md md:col-span-2 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold uppercase tracking-widest text-pink-200">Membership Tier</span>
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-[#D97706] text-white">
                            {{ $loyaltyTier ?? 'Gold' }} Member
                        </span>
                    </div>
                    <h2 class="text-3xl font-black font-display text-white mt-1">{{ number_format($loyaltyPoints ?? 2450) }} Points</h2>
                    <p class="text-xs text-pink-200 mt-2">Every visit and service earns you points toward complimentary beauty vouchers and discounts.</p>
                </div>

                <div class="pt-6 border-t border-white/20 mt-6 grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs text-pink-200 block">Total Visits</span>
                        <strong class="text-xl font-black text-white">{{ $customer->visit_count ?? 0 }} visits</strong>
                    </div>
                    <div>
                        <span class="text-xs text-pink-200 block">Total Invested</span>
                        <strong class="text-xl font-black text-white">₱{{ number_format($customer->total_spent ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200/90 p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-black text-gray-900 mb-2">Available Coupons</h3>
                    <p class="text-xs font-semibold text-gray-500 mb-4">Redeem at the counter during checkout.</p>
                    
                    <div class="space-y-3">
                        @forelse($availableRewards as $reward)
                            <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200">
                                <p class="text-sm font-black text-emerald-900">{{ $reward->reward_title }}</p>
                                <p class="text-xs font-bold text-emerald-700 mt-0.5">{{ $reward->discount_percentage }}% discount</p>
                            </div>
                        @empty
                            <p class="text-xs font-semibold text-gray-400 py-6 text-center">No active reward coupons at this moment.</p>
                        @endforelse
                    </div>
                </div>

                <button @click="customerView = 'booking'" class="btn btn-primary w-full text-xs font-bold py-2.5 mt-4">
                    Book to Earn More
                </button>
            </div>
        </div>
    </div>


    {{-- ====================================================================== --}}
    {{-- VIEW 6: NOTIFICATIONS                                                  --}}
    {{-- ====================================================================== --}}
    <div x-show="customerView === 'notifications'" x-cloak class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Notifications & Alerts</h1>
                <p class="text-sm font-semibold text-gray-500 mt-0.5">Stay up to date with salon confirmations, reminders, and updates.</p>
            </div>

            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="btn btn-secondary text-xs font-bold">
                    <i data-lucide="check-check" class="w-4 h-4"></i> Mark All as Read
                </button>
            </form>
        </div>

        <div class="bg-white rounded-3xl border border-gray-200/90 overflow-hidden shadow-xs">
            <ul class="divide-y divide-gray-100">
                @forelse($customerNotifications as $notif)
                    <li class="p-5 flex items-start justify-between gap-4 hover:bg-pink-50/30 transition {{ $notif->is_read ? 'bg-white' : 'bg-pink-50/20' }}">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-pink-100 text-[#7A1C49] flex items-center justify-center shrink-0">
                                <i data-lucide="bell" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="font-black text-base text-gray-900">{{ $notif->title }}</h4>
                                    @if(!$notif->is_read)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-[#D97706] text-white">NEW</span>
                                    @endif
                                </div>
                                <p class="text-sm font-semibold text-gray-600 mt-0.5">{{ $notif->message }}</p>
                                <span class="text-xs text-gray-400 font-bold mt-1 block">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        @if(!$notif->is_read)
                            <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-xl border border-gray-200 hover:bg-gray-100 text-xs font-bold text-gray-700 transition shrink-0">
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
        </div>
    </div>


    {{-- ====================================================================== --}}
    {{-- VIEW 7: PROFILE SETTINGS                                               --}}
    {{-- ====================================================================== --}}
    <div x-show="customerView === 'profile'" x-cloak class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Profile</h1>
            <p class="text-sm font-semibold text-gray-500 mt-0.5">Manage your personal contact information and password security.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Personal Information --}}
            <div class="bg-white rounded-3xl border border-gray-200/90 p-6 sm:p-7 shadow-xs">
                <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
                    <div class="w-14 h-14 rounded-full bg-[#7A1C49] text-white font-black text-xl flex items-center justify-center font-display">
                        {{ $initials ?? 'IL' }}
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900">{{ $customer->full_name ?? auth()->user()->name }}</h3>
                        <p class="text-xs text-gray-500 font-semibold">{{ $customer->email ?? auth()->user()->email }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('customer.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $customer->full_name ?? auth()->user()->name) }}" required class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone', $customer->phone ?? '') }}" required class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $customer->email ?? auth()->user()->email) }}" required class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Address</label>
                        <textarea name="address" rows="2" class="form-input">{{ old('address', $customer->address ?? '') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full font-bold">Save Changes</button>
                </form>
            </div>

            {{-- Password Change Form --}}
            <div class="bg-white rounded-3xl border border-gray-200/90 p-6 sm:p-7 shadow-xs">
                <h3 class="text-lg font-black text-gray-900 mb-2">Change Password</h3>
                <p class="text-xs font-semibold text-gray-500 mb-6">Ensure your account uses a secure password with letters and numbers.</p>

                <form method="POST" action="{{ route('customer.password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Current Password</label>
                        <input type="password" name="current_password" required class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">New Password</label>
                        <input type="password" name="password" minlength="8" required class="form-input" placeholder="At least 8 characters">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" minlength="8" required class="form-input">
                    </div>
                    <button type="submit" class="btn btn-secondary w-full font-bold">Update Password</button>
                </form>
            </div>
        </div>
    </div>


    {{-- ====================================================================== --}}
    {{-- VIEW 8: FEEDBACK & SERVICE RATINGS                                     --}}
    {{-- ====================================================================== --}}
    <div x-show="customerView === 'feedback'" x-cloak class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Rate Your Experience</h1>
            <p class="text-sm font-semibold text-gray-500 mt-0.5">Share your feedback to help us maintain the highest salon standards.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Rate Website --}}
            <div class="bg-white rounded-3xl border border-gray-200/90 p-6 shadow-xs">
                <h2 class="text-xl font-black text-gray-900">Rate the Puritas Salon Website</h2>
                <p class="text-xs font-semibold text-gray-500 mt-1 mb-5">Tell us how convenient the Puritas Salon portal is to use.</p>

                <form method="POST" action="{{ route('customer.ratings.website') }}" class="space-y-4">
                    @csrf
                    <label class="block text-xs font-black uppercase text-gray-600">Rating
                        <select name="rating" required class="form-select mt-1">
                            @for($rating = 5; $rating >= 1; $rating--)
                                <option value="{{ $rating }}" @selected(($websiteRating?->rating ?? 5) === $rating)>{{ $rating }} / 5 Stars</option>
                            @endfor
                        </select>
                    </label>
                    <label class="block text-xs font-black uppercase text-gray-600">Review Comments
                        <textarea name="comment" rows="4" maxlength="1000" class="form-input mt-1" placeholder="Share your booking experience">{{ old('comment', $websiteRating?->comment) }}</textarea>
                    </label>
                    <button type="submit" class="btn btn-primary w-full font-bold">Submit Website Rating</button>
                </form>
            </div>

            {{-- Rate Completed Services --}}
            <div class="bg-white rounded-3xl border border-gray-200/90 p-6 shadow-xs">
                <h2 class="text-xl font-black text-gray-900">Rate Completed Services</h2>
                <p class="text-xs font-semibold text-gray-500 mt-1 mb-5">Provide feedback on services you've received.</p>

                <div class="space-y-4">
                    @forelse($completedAppointments as $appointment)
                        @foreach($appointment->services as $service)
                            @php($existingRating = $serviceRatings->first(fn ($item) => $item->appointment_id === $appointment->id && $item->service_id === $service->id))
                            <form method="POST" action="{{ route('customer.ratings.service') }}" class="rounded-2xl border border-gray-200 p-4 bg-gray-50/50 space-y-3">
                                @csrf
                                <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-black text-gray-900">{{ $service->service_name }}</h4>
                                    @if($existingRating)
                                        <span class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-full">Rated</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <select name="rating" required class="form-select text-xs">
                                        @for($rating = 5; $rating >= 1; $rating--)
                                            <option value="{{ $rating }}" @selected(($existingRating?->rating ?? 5) === $rating)>{{ $rating }} / 5 Stars</option>
                                        @endfor
                                    </select>
                                    <input name="comment" value="{{ $existingRating?->comment }}" class="form-input text-xs" placeholder="Optional review">
                                </div>
                                <button type="submit" class="btn btn-secondary w-full text-xs font-bold py-2">
                                    {{ $existingRating ? 'Update Review' : 'Submit Review' }}
                                </button>
                            </form>
                        @endforeach
                    @empty
                        <p class="text-xs font-semibold text-gray-400 text-center py-8">Complete a salon appointment to leave a service rating.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>


    {{-- ====================================================================== --}}
    {{-- RESCHEDULE MODAL                                                       --}}
    {{-- ====================================================================== --}}
    <div x-show="rescheduleModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="rescheduleModalOpen = false"></div>
        <div class="relative bg-white rounded-3xl border border-gray-200 max-w-md w-full p-6 shadow-2xl z-10 space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-lg font-black text-gray-900">Reschedule Appointment</h3>
                <button @click="rescheduleModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <template x-if="activeAppointmentForReschedule">
                <form method="POST" :action="'/customer-appointments/' + activeAppointmentForReschedule.id + '/reschedule'" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-600 mb-1">New Date</label>
                        <input type="date" name="appointment_date" min="{{ now()->toDateString() }}" required class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-600 mb-1">New Start Time</label>
                        <input type="time" name="start_time" required class="form-input">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="rescheduleModalOpen = false" class="btn btn-secondary text-xs">Cancel</button>
                        <button type="submit" class="btn btn-primary text-xs">Confirm Reschedule</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
