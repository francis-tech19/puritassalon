<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? "Purita's Beauty Lounge - Salon Management" }}</title>

    <!-- Fonts & Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand-primary: #7A1C49;
            --brand-primary-dark: #5C1236;
            --brand-focus: rgba(122, 28, 73, 0.2);
            --brand-border: #7A1C49;
        }

        body[data-role="admin"] {
            --brand-primary: #312E81;
            --brand-primary-dark: #1E1B4B;
            --brand-focus: rgba(49, 46, 129, 0.2);
            --brand-border: #312E81;
        }

        body[data-role="staff"] {
            --brand-primary: #047857;
            --brand-primary-dark: #065F46;
            --brand-focus: rgba(4, 120, 87, 0.2);
            --brand-border: #047857;
        }

        .role-themed-border {
            border-color: var(--brand-border) !important;
        }

        .role-themed-bg {
            background-color: var(--brand-primary) !important;
        }

        .role-themed-text {
            color: var(--brand-primary) !important;
        }

        body[data-role="admin"] .salon-sidebar a[class*="bg-[#7A1C49]"] {
            background-color: #312E81 !important;
        }

        body[data-role="staff"] .salon-sidebar a[class*="bg-[#7A1C49]"] {
            background-color: #047857 !important;
        }

        /* Custom scrollbar for sidebar across browsers */
        .salon-sidebar {
            height: calc(100vh - 6.5rem) !important;
            max-height: calc(100vh - 6.5rem) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            position: sticky !important;
            top: 5.5rem !important;
            display: flex !important;
            flex-direction: column !important;
            scrollbar-width: thin !important;
            scrollbar-color: #CBD5E1 transparent !important;
        }

        @media (max-width: 767px) {
            .salon-sidebar {
                position: fixed !important;
                top: 5rem !important;
                left: 0 !important;
                width: min(16rem, 86vw) !important;
                height: calc(100vh - 5rem) !important;
                max-height: calc(100vh - 5rem) !important;
            }

            .app-header-inner {
                height: auto;
                min-height: 5rem;
                padding: 0.5rem 0.75rem;
                gap: 0.25rem;
            }

            .app-header-inner > div:first-child {
                flex: 1 1 auto;
                gap: 0.25rem;
            }

            .app-brand {
                min-width: 0;
                max-width: 3rem;
            }

            .app-brand span {
                display: none;
            }

            .app-brand img {
                width: 2.75rem;
                height: 2.75rem;
            }

            .app-header-controls {
                min-width: 0;
                flex: 0 0 auto;
                gap: 0.125rem;
            }

            .header-font-controls {
                display: none;
            }

            .header-profile-controls {
                gap: 0;
                padding-left: 0.25rem;
            }

            .app-shell {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
                gap: 0;
            }

            .app-shell main {
                width: 100%;
                min-width: 0;
            }
        }
        .salon-sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .salon-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .salon-sidebar::-webkit-scrollbar-thumb {
            background-color: #CBD5E1;
            border-radius: 9999px;
        }
        .salon-sidebar::-webkit-scrollbar-thumb:hover {
            background-color: #94A3B8;
        }
    </style>
</head>
@php
    $roleTheme = auth()->user()?->isAdmin() ? 'admin' : (auth()->user()?->isOwner() ? 'owner' : (auth()->user()?->isCustomer() ? 'customer' : 'staff'));
@endphp
<body data-role="{{ $roleTheme }}" class="bg-gray-100 text-gray-900 min-h-screen flex flex-col font-sans" x-data="{ sidebarOpen: false, currentScale: localStorage.getItem('salon_font_scale') || '1' }">

    <!-- Accessible Top Header -->
    <header class="bg-white border-b-4 role-themed-border sticky top-0 z-40 shadow-sm">
        <div class="app-header-inner max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-4 min-w-0">
                <!-- Mobile menu toggle -->
                <button @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen" aria-controls="mobileSidebar" class="md:hidden p-2 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#7A1C49]">
                    <i data-lucide="menu" class="w-7 h-7"></i>
                </button>

                <a href="{{ auth()->user()?->isCustomer() ? route('customer.dashboard') : (auth()->user()?->isStaff() ? route('employee.dashboard') : route('dashboard')) }}" class="app-brand flex items-center gap-2" aria-label="Purita's Beauty Lounge dashboard">
                    <img src="{{ asset('images/salon-logo.png') }}" alt="Purita's Beauty Lounge logo" class="w-14 h-14 object-contain shrink-0">
                </a>

                <!-- Role Badge -->
                @if(auth()->check())
                    @if(auth()->user()->isAdmin())
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-indigo-900 text-white shadow-sm">
                            <i data-lucide="shield" class="w-4 h-4"></i> ADMIN
                        </span>
                    @elseif(auth()->user()->isOwner())
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold role-themed-bg text-white shadow-sm">
                            <i data-lucide="crown" class="w-4 h-4"></i> OWNER
                        </span>
                    @elseif(auth()->user()->isCustomer())
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-amber-600 text-white shadow-sm">
                            <i data-lucide="heart" class="w-4 h-4"></i> CUSTOMER
                        </span>
                    @else
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-emerald-700 text-white shadow-sm">
                            <i data-lucide="scissors" class="w-4 h-4"></i> STAFF
                        </span>
                    @endif
                @endif
            </div>

            <!-- Accessibility & User Profile Controls -->
            <div class="app-header-controls flex items-center gap-3 sm:gap-6 shrink-0">
                <!-- Senior Text Size Resizer -->
                <div class="header-font-controls bg-[#FDF2F7] border border-pink-200 rounded-xl px-2.5 py-1 flex items-center gap-1">
                    <span class="text-xs font-bold text-[#7A1C49] mr-1 hidden sm:inline">Text Size:</span>
                    <button type="button" @click="setFontScale('0.9'); currentScale='0.9'" :class="currentScale === '0.9' ? 'bg-[#7A1C49] text-white' : 'text-gray-800 hover:bg-pink-100'" class="px-2 py-0.5 rounded text-xs font-bold transition">A-</button>
                    <button type="button" @click="setFontScale('1'); currentScale='1'" :class="currentScale === '1' ? 'bg-[#7A1C49] text-white' : 'text-gray-800 hover:bg-pink-100'" class="px-2 py-0.5 rounded text-sm font-bold transition">Normal</button>
                    <button type="button" @click="setFontScale('1.15'); currentScale='1.15'" :class="currentScale === '1.15' ? 'bg-[#7A1C49] text-white' : 'text-gray-800 hover:bg-pink-100'" class="px-2 py-0.5 rounded text-base font-bold transition">A+</button>
                    <button type="button" @click="setFontScale('1.3'); currentScale='1.3'" :class="currentScale === '1.3' ? 'bg-[#7A1C49] text-white' : 'text-gray-800 hover:bg-pink-100'" class="px-2 py-0.5 rounded text-lg font-bold transition">A++</button>
                </div>

                <!-- Notifications Bell -->
                @php
                    $unreadNotificationsQuery = \App\Models\Notification::where('is_read', false);
                    if (auth()->user()->isCustomer()) {
                        $unreadNotificationsQuery->where('user_id', auth()->id())->where('type', 'APPOINTMENT');
                    } elseif (auth()->user()->isStaff()) {
                        $unreadNotificationsQuery->where(function ($query): void {
                            $query->where('user_id', auth()->id())->orWhere(function ($globalQuery): void {
                                $globalQuery->whereNull('user_id')->where(function ($typeQuery): void {
                                    $typeQuery->where('type', '!=', 'APPOINTMENT')->orWhereHas('appointment', function ($appointmentQuery): void {
                                        $appointmentQuery->where('employee_id', auth()->user()->employee_id);
                                    });
                                });
                            });
                        });
                    } else {
                        $unreadNotificationsQuery->where(function ($query): void {
                            $query->whereNull('user_id')->orWhere('user_id', auth()->id());
                        });
                    }
                    $unreadNotificationsCount = $unreadNotificationsQuery->count();
                @endphp
                <a href="{{ route('notifications.index') }}" class="relative p-2 rounded-xl text-gray-700 hover:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-[#7A1C49]" title="Notifications">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                    @if($unreadNotificationsCount > 0)
                        <span id="notification-unread-count" class="absolute top-1 right-1 bg-red-600 text-white font-extrabold text-xs w-5 h-5 rounded-full flex items-center justify-center animate-pulse {{ $unreadNotificationsCount > 0 ? '' : 'hidden' }}">
                            {{ $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <!-- User Dropdown & Logout -->
                @if(auth()->check())
                    <div class="header-profile-controls flex items-center gap-3 border-l border-gray-300 pl-4">
                        <div class="hidden md:block text-right">
                            <div class="font-bold text-sm text-gray-900 leading-tight">{{ auth()->user()->name ?? auth()->user()->username }}</div>
                            <div class="text-xs font-semibold text-gray-500 uppercase">{{ auth()->user()->role->role_name ?? 'USER' }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="p-2 rounded-xl text-red-700 hover:bg-red-50 transition border border-red-200 flex items-center gap-1.5 font-bold text-sm" title="Log Out">
                                <i data-lucide="log-out" class="w-5 h-5"></i>
                                <span class="hidden lg:inline">Logout</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </header>

    <div class="app-shell flex-1 flex max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6 items-start">
        
         <!-- Mobile backdrop -->
         <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-black/50 backdrop-blur-[1px] md:hidden" @click="sidebarOpen = false"></div>

         <!-- Sidebar Navigation (Fixed in place, independently scrollable) -->
         <aside id="mobileSidebar" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
             data-sidebar-scroll="main"
             class="salon-sidebar w-64 shrink-0 transition-transform duration-250 ease-out fixed inset-y-0 left-0 z-50 bg-white p-6 shadow-2xl md:shadow-none md:static md:translate-x-0 md:transform-none md:transition-none md:bg-white md:p-3 md:rounded-2xl md:border md:border-gray-200">
            <div class="flex justify-between items-center md:hidden mb-4 pb-2 border-b">
                <span class="font-bold text-lg text-[#7A1C49]">Salon Modules</span>
                <button @click="sidebarOpen = false" class="text-gray-500 hover:text-gray-800">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="mb-4 grid grid-cols-4 gap-2 md:hidden" aria-label="Text size controls">
                <button type="button" @click="setFontScale('0.9'); currentScale='0.9'" :aria-pressed="currentScale === '0.9'" class="min-h-11 rounded-lg border border-pink-200 bg-pink-50 text-sm font-bold text-[#7A1C49]">A-</button>
                <button type="button" @click="setFontScale('1'); currentScale='1'" :aria-pressed="currentScale === '1'" class="min-h-11 rounded-lg border border-pink-200 bg-pink-50 text-sm font-bold text-[#7A1C49]">Normal</button>
                <button type="button" @click="setFontScale('1.15'); currentScale='1.15'" :aria-pressed="currentScale === '1.15'" class="min-h-11 rounded-lg border border-pink-200 bg-pink-50 text-sm font-bold text-[#7A1C49]">A+</button>
                <button type="button" @click="setFontScale('1.3'); currentScale='1.3'" :aria-pressed="currentScale === '1.3'" class="min-h-11 rounded-lg border border-pink-200 bg-pink-50 text-sm font-bold text-[#7A1C49]">A++</button>
            </div>

            <nav class="space-y-1" @click="sidebarOpen = false">
                @php
                    $user = auth()->user();
                    $isOwner = $user && $user->isOwner();
                    $isAdmin = $user && $user->isAdmin();
                    $isCustomer = $user && $user->isCustomer();
                    $isStaff = $user && $user->isStaff();
                    $isOwnerOrAdmin = $isOwner || $isAdmin;
                @endphp

                @if($isCustomer)
                    <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('customer.dashboard') ? 'bg-amber-600 text-white shadow' : 'text-gray-700 hover:bg-amber-50 hover:text-amber-700' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span>My Dashboard</span>
                    </a>
                    <a href="{{ route('customer.dashboard', ['view' => 'booking']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold {{ request('view') === 'booking' ? 'bg-amber-600 text-white shadow' : 'text-gray-700 hover:bg-amber-50 hover:text-amber-700' }} transition">
                        <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                        <span>Book Appointment</span>
                    </a>
                    <a href="{{ route('customer.dashboard', ['view' => 'appointments']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold {{ request('view') === 'appointments' ? 'bg-amber-600 text-white shadow' : 'text-gray-700 hover:bg-amber-50 hover:text-amber-700' }} transition">
                        <i data-lucide="calendar-check" class="w-5 h-5"></i>
                        <span>My Appointments</span>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-gray-700 transition hover:bg-amber-50 hover:text-amber-700">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span>Notifications</span>
                    </a>
                    <a href="{{ route('customer.dashboard', ['view' => 'loyalty']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold {{ request('view') === 'loyalty' ? 'bg-amber-600 text-white shadow' : 'text-gray-700 hover:bg-amber-50 hover:text-amber-700' }} transition">
                        <i data-lucide="award" class="w-5 h-5"></i>
                        <span>Loyalty Rewards</span>
                    </a>
                    <a href="{{ route('customer.dashboard', ['view' => 'profile']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold {{ request('view') === 'profile' ? 'bg-amber-600 text-white shadow' : 'text-gray-700 hover:bg-amber-50 hover:text-amber-700' }} transition">
                        <i data-lucide="user-round" class="w-5 h-5"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="{{ route('customer.dashboard', ['view' => 'feedback']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold {{ request('view') === 'feedback' ? 'bg-amber-600 text-white shadow' : 'text-gray-700 hover:bg-amber-50 hover:text-amber-700' }} transition">
                        <i data-lucide="star" class="w-5 h-5"></i>
                        <span>Rate Experience</span>
                    </a>
                @endif

                @if(!$isCustomer && $isOwnerOrAdmin)
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('dashboard') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span>Dashboard</span>
                    </a>
                @endif

                @if($isStaff)
                    <a href="{{ route('employee.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('employee.dashboard') ? 'bg-emerald-700 text-white shadow' : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-800' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span>My Dashboard</span>
                    </a>
                @endif

                @if(!$isCustomer && $isAdmin)
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-900 text-white shadow' : 'text-indigo-900 hover:bg-indigo-50' }}">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                        <span>Admin Console</span>
                    </a>
                @endif

                @if(!$isCustomer)
                <div class="pt-2 pb-1">
                    <span class="px-4 text-xs font-black uppercase tracking-wider text-gray-400">Front Desk & Services</span>
                </div>

                <a href="{{ route('appointments.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('appointments.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                    <span>Appointments</span>
                </a>

                <a href="{{ route('sales.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('sales.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    <span>Point of Sale</span>
                </a>

                <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('customers.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Customers</span>
                </a>

                <a href="{{ route('services.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('services.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                    <span>Services</span>
                </a>

                <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('inventory.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                    <i data-lucide="package" class="w-5 h-5"></i>
                    <span>Inventory</span>
                </a>

                <a href="{{ route('loyalty.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('loyalty.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                    <i data-lucide="award" class="w-5 h-5"></i>
                    <span>Loyalty Rewards</span>
                </a>

                @if($isOwnerOrAdmin)
                    <div class="pt-3 pb-1">
                        <span class="px-4 text-xs font-black uppercase tracking-wider text-gray-400">Owner Management</span>
                    </div>

                    <a href="{{ route('employees.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('employees.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                        <i data-lucide="user-check" class="w-5 h-5"></i>
                        <span>Employees</span>
                    </a>

                    <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('expenses.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                        <span>Expenses</span>
                    </a>

                    <a href="{{ route('analytics.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('analytics.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                        <span>Analytics</span>
                    </a>

                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('reports.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                        <span>Reports</span>
                    </a>

                    <a href="{{ route('ai.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('ai.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                        <i data-lucide="bot" class="w-5 h-5"></i>
                        <span>AI Assistant</span>
                    </a>

                    <a href="{{ route('audit.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('audit.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                        <span>Audit Logs</span>
                    </a>

                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('settings.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                        <span>Settings</span>
                    </a>

                    @if($isOwner)
                        <a href="{{ route('service-ratings.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('service-ratings.*') ? 'bg-[#7A1C49] text-white shadow' : 'text-gray-700 hover:bg-pink-50 hover:text-[#7A1C49]' }}">
                            <i data-lucide="star" class="w-5 h-5"></i>
                            <span>Service Ratings</span>
                        </a>
                    @endif

                    @if($isAdmin)
                        <a href="{{ route('admin.website-ratings.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition {{ request()->routeIs('admin.website-ratings.*') ? 'bg-indigo-900 text-white shadow' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-900' }}">
                            <i data-lucide="message-square-heart" class="w-5 h-5"></i>
                            <span>Website Feedback</span>
                        </a>
                    @endif
                @endif
                @endif
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 min-w-0">
            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border-2 border-emerald-500 text-emerald-900 font-bold flex items-center gap-3 shadow-sm" role="alert">
                    <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-600 shrink-0"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border-2 border-red-500 text-red-900 font-bold flex items-center gap-3 shadow-sm" role="alert">
                    <i data-lucide="alert-circle" class="w-6 h-6 text-red-600 shrink-0"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-amber-50 border-2 border-amber-500 text-amber-900 font-bold shadow-sm">
                    <div class="flex items-center gap-2 mb-1">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
                        <span>Please correct the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside text-sm font-semibold pl-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Footer -->
    <footer class="mt-auto py-6 border-t border-gray-300 bg-white text-center text-sm font-semibold text-gray-500">
        <p>&copy; {{ date('Y') }} Purita's Beauty Lounge Salon Management System. Built with Laravel 12 & Tailwind CSS.</p>
    </footer>

    @stack('scripts')
    <div id="live-notification-stack" data-poll-url="{{ route('notifications.poll') }}" class="fixed right-4 top-20 z-[100] w-[min(24rem,calc(100vw-2rem))] space-y-2" aria-live="polite"></div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const badge = document.getElementById('notification-unread-count');
            const stack = document.getElementById('live-notification-stack');
            const pollUrl = stack.dataset.pollUrl;
            let lastId = Number(sessionStorage.getItem('salonLastNotificationId') || 0);
            let initialized = lastId > 0;

            const poll = async () => {
                try {
                    const response = await fetch(pollUrl + '?after=' + lastId, { cache: 'no-store', headers: { Accept: 'application/json' } });
                    if (!response.ok) return;
                    const result = await response.json();
                    if (badge) {
                        badge.textContent = result.unread_count;
                        badge.classList.toggle('hidden', result.unread_count === 0);
                    }
                    for (const notification of result.data) {
                        lastId = Math.max(lastId, notification.id);
                        sessionStorage.setItem('salonLastNotificationId', String(lastId));
                        if (!initialized) continue;
                        const toast = document.createElement('a');
                        toast.href = notification.url;
                        toast.className = 'block rounded-xl border border-amber-300 bg-white p-4 shadow-lg';
                        const title = document.createElement('strong');
                        title.className = 'block text-sm text-gray-900';
                        title.textContent = notification.title;
                        const message = document.createElement('span');
                        message.className = 'mt-1 block text-xs text-gray-600';
                        message.textContent = notification.message;
                        toast.append(title, message);
                        stack.prepend(toast);
                        window.setTimeout(() => toast.remove(), 8000);
                    }
                    initialized = true;
                } catch (error) {
                    console.error('Unable to refresh notifications.', error);
                }
            };

            poll();
            window.setInterval(poll, 30000);
        });
    </script>
</body>
</html>
