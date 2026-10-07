<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Puritas Salon - Customer Portal' }}</title>

    <!-- Google Fonts: Playfair Display for elegant salon headlines & Instrument Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand-plum: #7A1C49;
            --brand-plum-dark: #5C1236;
            --brand-plum-deep: #3D0B24;
            --brand-gold: #D97706;
            --brand-gold-dark: #B45309;
            --brand-bg: #F4F5F7;
        }

        body {
            background-color: var(--brand-bg);
            color: #111827;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .font-display {
            font-family: 'Playfair Display', Georgia, serif;
        }

        /* Senior text scaling */
        .fsalon-scale-ctrl {
            font-size: 0.875rem;
        }

        /* Custom scrollbars */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 9999px;
        }
    </style>
</head>
<body class="min-h-screen bg-[#F4F5F7] text-[#111827] antialiased" x-data="{ mobileNav: false, currentScale: localStorage.getItem('salon_font_scale') || '1' }">
    @php
        $authUser = auth()->user();
        $customer = $customer ?? $authUser?->customer;
        if (!isset($initials) && $authUser) {
            $nameParts = preg_split('/\s+/', trim(($customer->full_name ?? $authUser->name) ?: 'Customer'));
            $initials = count($nameParts) >= 2
                ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
                : strtoupper(substr($nameParts[0] ?? 'C', 0, 2));
        }
        if (!isset($unreadNotificationsCount) && $authUser) {
            $unreadNotificationsCount = \App\Models\Notification::where('user_id', $authUser->id)->where('is_read', false)->count();
        }
    @endphp

    <div class="flex min-h-screen w-full">
        <!-- Desktop / Tablet Plum Sidebar -->
        <aside data-sidebar-scroll="customer-desktop" class="hidden md:flex md:w-64 lg:w-72 bg-[#7A1C49] flex-col justify-between shrink-0 sticky top-0 self-start h-screen overflow-y-auto z-20 shadow-xl border-r border-[#5C1236]/40 select-none">
            <div class="p-6 flex flex-col h-full justify-between">
                <div>
                    <!-- Brand Header -->
                    <div class="flex items-center gap-3.5 mb-8">
                        <div class="w-11 h-11 rounded-2xl bg-white flex items-center justify-center shadow-md shrink-0">
                            <span class="text-2xl font-black font-display text-[#7A1C49] leading-none">P</span>
                        </div>
                        <div>
                            <span class="block text-2xl font-black tracking-tight text-white leading-tight font-display">Puritas Salon</span>
                            <span class="block text-xs font-semibold text-pink-200 uppercase tracking-wider">Customer</span>
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    @php
                        $currentView = request('view', 'home');
                    @endphp
                    <nav class="space-y-2" @click="mobileNav = false; sidebarOpen = false">
                        <a href="{{ route('customer.dashboard') }}" 
                           class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-sm transition-all duration-150 {{ $currentView === 'home' ? 'bg-white text-[#7A1C49] shadow-md' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                            <i data-lucide="home" class="w-5 h-5 {{ $currentView === 'home' ? 'text-[#7A1C49]' : 'text-pink-200' }}"></i>
                            <span>My Dashboard</span>
                        </a>

                        <a href="{{ route('customer.dashboard', ['view' => 'booking']) }}" 
                           class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-sm transition-all duration-150 {{ $currentView === 'booking' ? 'bg-white text-[#7A1C49] shadow-md' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                            <i data-lucide="calendar-plus" class="w-5 h-5 {{ $currentView === 'booking' ? 'text-[#7A1C49]' : 'text-pink-200' }}"></i>
                            <span>Book Appointment</span>
                        </a>

                        <a href="{{ route('customer.dashboard', ['view' => 'appointments']) }}" 
                           class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-sm transition-all duration-150 {{ $currentView === 'appointments' ? 'bg-white text-[#7A1C49] shadow-md' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                            <i data-lucide="clipboard-list" class="w-5 h-5 {{ $currentView === 'appointments' ? 'text-[#7A1C49]' : 'text-pink-200' }}"></i>
                            <span>My Appointments</span>
                        </a>

                        <a href="{{ route('customer.dashboard', ['view' => 'services']) }}" 
                           class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-sm transition-all duration-150 {{ $currentView === 'services' ? 'bg-white text-[#7A1C49] shadow-md' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                            <i data-lucide="sparkles" class="w-5 h-5 {{ $currentView === 'services' ? 'text-[#7A1C49]' : 'text-pink-200' }}"></i>
                            <span>Services</span>
                        </a>

                        <a href="{{ route('customer.dashboard', ['view' => 'loyalty']) }}" 
                           class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-sm transition-all duration-150 {{ $currentView === 'loyalty' ? 'bg-white text-[#7A1C49] shadow-md' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                            <i data-lucide="gift" class="w-5 h-5 {{ $currentView === 'loyalty' ? 'text-[#7A1C49]' : 'text-pink-200' }}"></i>
                            <span>Loyalty Rewards</span>
                        </a>

                        <a href="{{ route('customer.dashboard', ['view' => 'notifications']) }}" 
                           class="flex items-center justify-between px-4 py-3 rounded-xl font-bold text-sm transition-all duration-150 {{ $currentView === 'notifications' ? 'bg-white text-[#7A1C49] shadow-md' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                            <div class="flex items-center gap-3.5">
                                <i data-lucide="bell" class="w-5 h-5 {{ $currentView === 'notifications' ? 'text-[#7A1C49]' : 'text-pink-200' }}"></i>
                                <span>Notifications</span>
                            </div>
                            @if(!empty($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-xs font-black bg-[#D97706] text-white shadow-sm">
                                    {{ $unreadNotificationsCount }}
                                </span>
                            @endif
                        </a>

                        <a href="{{ route('customer.dashboard', ['view' => 'profile']) }}" 
                           class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-bold text-sm transition-all duration-150 {{ $currentView === 'profile' ? 'bg-white text-[#7A1C49] shadow-md' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                            <i data-lucide="user" class="w-5 h-5 {{ $currentView === 'profile' ? 'text-[#7A1C49]' : 'text-pink-200' }}"></i>
                            <span>Profile</span>
                        </a>
                    </nav>
                </div>

                <!-- Bottom User / Logout Pill -->
                <div class="pt-6 border-t border-white/15">
                    <!-- Senior Text Size Resizer -->
                    <div class="mb-4 bg-black/20 rounded-xl p-1.5 flex items-center justify-between text-white/90">
                        <span class="text-xs font-semibold px-2 text-pink-200">Text:</span>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="setFontScale('0.9'); currentScale='0.9'" :class="currentScale === '0.9' ? 'bg-white text-[#7A1C49]' : 'hover:bg-white/20'" class="px-2 py-0.5 rounded text-xs font-bold transition">A-</button>
                            <button type="button" @click="setFontScale('1'); currentScale='1'" :class="currentScale === '1' ? 'bg-white text-[#7A1C49]' : 'hover:bg-white/20'" class="px-2 py-0.5 rounded text-xs font-bold transition">Def</button>
                            <button type="button" @click="setFontScale('1.15'); currentScale='1.15'" :class="currentScale === '1.15' ? 'bg-white text-[#7A1C49]' : 'hover:bg-white/20'" class="px-2 py-0.5 rounded text-xs font-bold transition">A+</button>
                            <button type="button" @click="setFontScale('1.3'); currentScale='1.3'" :class="currentScale === '1.3' ? 'bg-white text-[#7A1C49]' : 'hover:bg-white/20'" class="px-2 py-0.5 rounded text-xs font-bold transition">A++</button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 p-2 rounded-xl bg-black/15">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-[#3D0B24] border border-pink-300/40 text-white font-black text-sm flex items-center justify-center shrink-0">
                                {{ $initials ?? 'IL' }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-white leading-tight truncate">
                                    {{ $customer->full_name ?? auth()->user()->name ?? 'Customer' }}
                                </p>
                                <p class="text-xs text-pink-200 font-medium">Customer</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="p-2 text-pink-200 hover:text-white hover:bg-white/10 rounded-lg transition" title="Log out">
                                <i data-lucide="power" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileNav" x-cloak class="fixed inset-0 z-50 md:hidden">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="mobileNav = false"></div>
            <div data-sidebar-scroll="customer-mobile" class="fixed inset-y-0 left-0 w-72 overflow-y-auto bg-[#7A1C49] p-6 text-white shadow-2xl flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow">
                                <span class="text-xl font-black font-display text-[#7A1C49]">P</span>
                            </div>
                            <div>
                                <span class="block text-xl font-bold font-display text-white">Puritas Salon</span>
                                <span class="block text-xs text-pink-200 uppercase">Customer</span>
                            </div>
                        </div>
                        <button @click="mobileNav = false" class="p-2 text-pink-200 hover:text-white">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <nav class="space-y-2" @click="mobileNav = false; sidebarOpen = false">
                        <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm {{ $currentView === 'home' ? 'bg-white text-[#7A1C49]' : 'text-white hover:bg-white/10' }}">
                            <i data-lucide="home" class="w-5 h-5"></i>
                            <span>My Dashboard</span>
                        </a>
                        <a href="{{ route('customer.dashboard', ['view' => 'booking']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm {{ $currentView === 'booking' ? 'bg-white text-[#7A1C49]' : 'text-white hover:bg-white/10' }}">
                            <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                            <span>Book Appointment</span>
                        </a>
                        <a href="{{ route('customer.dashboard', ['view' => 'appointments']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm {{ $currentView === 'appointments' ? 'bg-white text-[#7A1C49]' : 'text-white hover:bg-white/10' }}">
                            <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                            <span>My Appointments</span>
                        </a>
                        <a href="{{ route('customer.dashboard', ['view' => 'services']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm {{ $currentView === 'services' ? 'bg-white text-[#7A1C49]' : 'text-white hover:bg-white/10' }}">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                            <span>Services</span>
                        </a>
                        <a href="{{ route('customer.dashboard', ['view' => 'loyalty']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm {{ $currentView === 'loyalty' ? 'bg-white text-[#7A1C49]' : 'text-white hover:bg-white/10' }}">
                            <i data-lucide="gift" class="w-5 h-5"></i>
                            <span>Loyalty Rewards</span>
                        </a>
                        <a href="{{ route('customer.dashboard', ['view' => 'notifications']) }}" class="flex items-center justify-between px-4 py-3 rounded-xl font-bold text-sm {{ $currentView === 'notifications' ? 'bg-white text-[#7A1C49]' : 'text-white hover:bg-white/10' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="bell" class="w-5 h-5"></i>
                                <span>Notifications</span>
                            </div>
                            @if(!empty($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-xs font-black bg-[#D97706] text-white">
                                    {{ $unreadNotificationsCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('customer.dashboard', ['view' => 'profile']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm {{ $currentView === 'profile' ? 'bg-white text-[#7A1C49]' : 'text-white hover:bg-white/10' }}">
                            <i data-lucide="user" class="w-5 h-5"></i>
                            <span>Profile</span>
                        </a>
                    </nav>
                </div>

                <div class="pt-6 border-t border-white/20">
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-black/20">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-[#3D0B24] border border-pink-300 text-white font-bold flex items-center justify-center shrink-0">
                                {{ $initials ?? 'IL' }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-white truncate">{{ $customer->full_name ?? auth()->user()->name ?? 'Customer' }}</p>
                                <p class="text-xs text-pink-200">Customer</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="p-2 text-pink-200 hover:text-white">
                                <i data-lucide="power" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Mobile Header Bar -->
            <header class="md:hidden bg-[#7A1C49] text-white px-4 py-3 flex items-center justify-between sticky top-0 z-20 shadow-md">
                <button @click="mobileNav = true" class="p-2 rounded-xl text-white hover:bg-white/10" aria-label="Open menu">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">
                        <span class="text-lg font-black font-display text-[#7A1C49]">P</span>
                    </div>
                    <span class="text-xl font-bold font-display tracking-tight">Puritas Salon</span>
                </div>
                <a href="{{ route('customer.dashboard', ['view' => 'notifications']) }}" class="p-2 relative rounded-xl text-white hover:bg-white/10">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                    @if(!empty($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                        <span class="absolute top-1 right-1 bg-[#D97706] text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center">
                            {{ $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>
            </header>

            <!-- Main Canvas Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <!-- Alerts / Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-500 text-emerald-900 font-bold flex items-center gap-3 shadow-xs" role="alert">
                        <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-600 shrink-0"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-red-50 border-2 border-red-500 text-red-900 font-bold flex items-center gap-3 shadow-xs" role="alert">
                        <i data-lucide="alert-circle" class="w-6 h-6 text-red-600 shrink-0"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-amber-50 border-2 border-amber-500 text-amber-900 font-bold shadow-xs" role="alert">
                        <div class="flex items-center gap-2 mb-2">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0"></i>
                            <span class="font-black">Please note:</span>
                        </div>
                        <ul class="list-disc list-inside text-sm font-semibold pl-2 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
