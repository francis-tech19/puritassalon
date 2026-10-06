<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $settings?->salon_name ?? "Purita's Beauty Lounge" }} - beauty and wellness services with easy online appointment requests.">
    <title>{{ $settings?->salon_name ?? "Purita's Beauty Lounge" }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if ('scrollRestoration' in window.history) {
            window.history.scrollRestoration = 'manual';
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap');
        :root { --wine: #4d071d; --wine-deep: #26030f; --gold: #e8c36c; --ink: #31131d; }
        html { scroll-behavior: smooth; }
        body { margin: 0; color: var(--ink); background: #fbf8f5; font-family: 'DM Sans', sans-serif; }
        h1, h2, h3 { font-family: 'Playfair Display', Georgia, serif; }
        .home-nav { background: rgba(38, 3, 15, .92); backdrop-filter: blur(14px); }
        .hero { background: linear-gradient(90deg, rgba(38, 3, 15, .98) 0%, rgba(77, 7, 29, .86) 35%, rgba(38, 3, 15, .12) 76%), url('/images/background.png') center / cover; }
        .hero-copy { animation: rise-in .8s ease-out both; }
        .service-card { transition: transform .2s ease, box-shadow .2s ease; }
        .service-card:hover { transform: translateY(-5px); box-shadow: 0 18px 34px rgba(77, 7, 29, .14); }
        .service-photo { position: relative; }
        .service-fallback { display: flex; align-items: center; justify-content: center; background: #f8eeec; }
        .service-fallback::before { content: ''; position: absolute; inset: 0; opacity: .9; }
        .service-fallback.hair-care::before { background: linear-gradient(135deg, #5c0e2a 0%, #b35a66 52%, #e8c36c 100%); }
        .service-fallback.nail-care::before { background: linear-gradient(135deg, #7a1c49 0%, #d98a9f 58%, #f4d582 100%); }
        .service-fallback.facial-skin::before { background: linear-gradient(135deg, #8b4d3b 0%, #d99b78 55%, #f5dfbd 100%); }
        .service-fallback i { position: relative; z-index: 1; color: rgba(255, 255, 255, .92); filter: drop-shadow(0 3px 8px rgba(38, 3, 15, .25)); }
        .gold-line { background: linear-gradient(90deg, var(--gold), transparent); }
        .step-number { box-shadow: 0 0 0 7px rgba(232, 195, 108, .13); }
        .mobile-menu { display: none; }
        @keyframes rise-in { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } *, *::before, *::after { animation: none !important; transition: none !important; } }
        @media (max-width: 767px) { .mobile-menu { display: block; } }
    </style>
</head>
<body>
    <header class="home-nav fixed inset-x-0 top-0 z-50 border-b border-white/15 text-white">
        <div class="mx-auto flex h-[76px] max-w-7xl items-center justify-between px-5 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="{{ $settings?->salon_name ?? "Purita's Beauty Lounge" }} home">
                <img src="{{ asset('images/salon-logo.png') }}" alt="" class="h-12 w-12 object-contain">
                <span class="hidden text-sm font-semibold uppercase tracking-[.22em] text-[#e8c36c] sm:block">{{ $settings?->salon_name ?? "Purita's Beauty Lounge" }}</span>
            </a>
            <nav class="hidden items-center gap-8 text-sm font-semibold md:flex" aria-label="Main navigation">
                <a href="{{ route('home', ['section' => 'home']) }}" class="{{ $activeSection === 'home' ? 'text-[#e8c36c]' : 'text-white/80 transition hover:text-white' }}">Home</a>
                <a href="{{ route('home', ['section' => 'services']) }}" class="{{ $activeSection === 'services' ? 'text-[#e8c36c]' : 'text-white/80 transition hover:text-white' }}">Services</a>
                <a href="{{ route('home', ['section' => 'about']) }}" class="{{ $activeSection === 'about' ? 'text-[#e8c36c]' : 'text-white/80 transition hover:text-white' }}">About</a>
                <a href="{{ route('home', ['section' => 'contact']) }}" class="{{ $activeSection === 'contact' ? 'text-[#e8c36c]' : 'text-white/80 transition hover:text-white' }}">Contact</a>
            </nav>
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" class="mobile-menu rounded-lg border border-white/30 p-2 text-white" aria-expanded="false" aria-controls="mobileNav" aria-label="Open menu"><i data-lucide="menu" class="h-5 w-5"></i></button>
                <a href="{{ route('login') }}" class="rounded-full border border-[#e8c36c]/70 px-4 py-2 text-xs font-bold text-white transition hover:bg-white/10 sm:px-6 sm:text-sm">Log in</a>
                <a href="{{ route('register') }}" class="rounded-full bg-[#f4d582] px-4 py-2 text-xs font-bold text-[#3d0b18] shadow-lg transition hover:bg-white sm:px-6 sm:text-sm">Create Account</a>
            </div>
        </div>
        <nav id="mobileNav" class="hidden border-t border-white/15 px-5 py-3 md:hidden" aria-label="Mobile navigation">
            <div class="flex flex-col gap-3 text-sm font-semibold"><a href="{{ route('home', ['section' => 'home']) }}" class="text-white/80">Home</a><a href="{{ route('home', ['section' => 'services']) }}" class="text-white/80">Services</a><a href="{{ route('home', ['section' => 'about']) }}" class="text-white/80">About</a><a href="{{ route('home', ['section' => 'contact']) }}" class="text-white/80">Contact</a></div>
        </nav>
    </header>

    <main>
        @if($activeSection === 'home')
        <section id="home" class="hero min-h-[620px] pt-[76px] text-white">
            <div class="mx-auto flex min-h-[544px] max-w-7xl items-center px-5 py-20 lg:px-8">
                <div class="hero-copy max-w-xl">
                    <p class="mb-3 text-sm font-bold uppercase tracking-[.24em] text-[#f4d582]">Welcome to {{ $settings?->salon_name ?? "Purita's Beauty Lounge" }}</p>
                    <h1 class="text-5xl leading-[1.05] sm:text-6xl lg:text-7xl">Look good.<br><span class="text-[#f4d582]">Feel good.</span><br>Be you.</h1>
                    <div class="gold-line my-6 h-px w-32"></div>
                    <p class="max-w-md text-base leading-7 text-white/80 sm:text-lg">Professional beauty and wellness services in a relaxing, elegant environment. Your beauty is our passion.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('register', ['return' => 'home']) }}" class="action-link inline-flex min-h-12 items-center gap-2 rounded-xl bg-[#f4d582] px-6 font-bold text-[#3d0b18] transition hover:bg-white"><i data-lucide="calendar-days" class="h-5 w-5"></i> Book Now</a>
                        <a href="{{ route('home', ['section' => 'services']) }}" class="inline-flex min-h-12 items-center gap-2 rounded-xl border border-white/60 px-6 font-bold text-white transition hover:bg-white/10"><i data-lucide="sparkles" class="h-5 w-5"></i> View Services</a>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @if($activeSection === 'services')
        <section id="services" class="mx-auto max-w-7xl px-5 pb-16 pt-[112px] lg:px-8">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div><p class="text-sm font-bold uppercase tracking-[.2em] text-[#9b1c58]">Our menu</p><h2 class="mt-1 text-4xl text-[#4d071d]">Featured Services</h2><p class="mt-2 text-gray-600">Thoughtfully designed services to bring out your natural beauty.</p></div>
                <span class="hidden text-sm font-bold text-[#9b1c58] sm:block">{{ $services->count() }} services available</span>
            </div>
            @if($services->isNotEmpty())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($services as $service)
                        @php($serviceImage = [
                            'Haircut & Blowdry (Women)' => 'haircut.png',
                            'Haircut (Men)' => 'haircut.png',
                            'Full Hair Coloring' => 'hair-coloring.png',
                            'Keratin Hair Rebonding' => 'rebonding.png',
                            'Classic Manicure' => 'manicure.png',
                            'Classic Pedicure' => 'pedicure.png',
                            'Gel Manicure & Pedicure Combo' => 'manicure.png',
                            'Facial Deep Cleansing' => 'facial.png',
                        ][$service->service_name] ?? null)
                        <article class="service-card overflow-hidden rounded-2xl border border-[#eadfda] bg-white shadow-sm">
                            <div class="service-photo relative h-40 overflow-hidden">
                                @if($service->photo_path)
                                    <img src="{{ asset('storage/' . $service->photo_path) }}" alt="{{ $service->service_name }}" class="h-full w-full object-cover">
                                @elseif($serviceImage)
                                    <img src="{{ asset('images/' . $serviceImage) }}" alt="{{ $service->service_name }}" class="h-full w-full object-cover">
                                @else
                                        @php($categoryClass = \Illuminate\Support\Str::slug($service->category))
                                    <div class="service-fallback {{ $categoryClass }} h-full w-full" aria-label="{{ $service->category }} service image placeholder">
                                        <i data-lucide="{{ $categoryClass === 'hair-care' ? 'scissors' : ($categoryClass === 'nail-care' ? 'hand' : 'sparkles') }}" class="h-14 w-14"></i>
                                    </div>
                                @endif
                                <span class="absolute bottom-3 left-3 rounded-full bg-white/90 px-3 py-1 text-xs font-bold uppercase tracking-wider text-[#7a1c49]">{{ $service->category }}</span>
                            </div>
                            <div class="p-5"><h3 class="text-xl text-[#4d071d]">{{ $service->service_name }}</h3><p class="mt-2 min-h-12 text-sm leading-5 text-gray-600">{{ $service->description ?: 'Professional care from our trained beauty team.' }}</p><div class="mt-4 flex items-center justify-between border-t border-[#f0e8e4] pt-4"><strong class="text-lg text-[#7a1c49]">&#8369;{{ number_format($service->price, 2) }}</strong><span class="flex items-center gap-1 text-xs font-semibold text-gray-500"><i data-lucide="clock-3" class="h-4 w-4"></i>{{ $service->duration_minutes }} min</span></div><a href="{{ route('register', ['service' => $service->id, 'return' => 'home?section=services']) }}" class="action-link mt-4 flex min-h-11 items-center justify-center rounded-lg bg-[#8d123f] text-sm font-bold text-white transition hover:bg-[#5c1236]">Book this service</a></div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="rounded-xl bg-white p-8 text-center text-gray-600">Our service menu is being refreshed. Please contact us to book.</p>
            @endif
        </section>
        @endif

        @if($activeSection === 'about')
        <section id="about" class="border-y border-[#ead8d5] bg-[#f8eeec] px-5 pb-10 pt-[112px] lg:px-8">
            <div class="mx-auto grid max-w-7xl gap-8 md:grid-cols-[1.2fr_3fr]">
                <h2 class="text-3xl text-[#4d071d]">Why choose {{ $settings?->salon_name ?? "Purita's Beauty Lounge" }}?</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex gap-3"><i data-lucide="scissors" class="h-7 w-7 shrink-0 text-[#8d123f]"></i><div><strong class="text-sm">Professional Stylists</strong><p class="mt-1 text-xs text-gray-600">Skilled and experienced beauty professionals.</p></div></div>
                    <div class="flex gap-3"><i data-lucide="calendar-check-2" class="h-7 w-7 shrink-0 text-[#8d123f]"></i><div><strong class="text-sm">Easy Online Booking</strong><p class="mt-1 text-xs text-gray-600">Request appointments in just a few clicks.</p></div></div>
                    <div class="flex gap-3"><i data-lucide="star" class="h-7 w-7 shrink-0 text-[#8d123f]"></i><div><strong class="text-sm">Loyalty Rewards</strong><p class="mt-1 text-xs text-gray-600">Earn benefits from every visit.</p></div></div>
                    <div class="flex gap-3"><i data-lucide="history" class="h-7 w-7 shrink-0 text-[#8d123f]"></i><div><strong class="text-sm">Appointment History</strong><p class="mt-1 text-xs text-gray-600">Keep track of past and upcoming visits.</p></div></div>
                </div>
            </div>
        </section>

        <section class="bg-[#330510] px-5 py-16 text-white lg:px-8">
            <div class="mx-auto max-w-4xl">
                <p class="text-sm font-bold uppercase tracking-[.2em] text-[#f4d582]">Simple booking</p>
                <h2 class="mt-2 text-4xl">How It Works</h2>
                <div class="mt-8 grid gap-7 sm:grid-cols-2">
                    <div class="flex gap-4"><span class="step-number flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f4d582] font-bold text-[#4d071d]">01</span><div><strong>Create an Account</strong><p class="mt-1 text-sm text-white/65">Sign up with your details.</p></div></div>
                    <div class="flex gap-4"><span class="step-number flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f4d582] font-bold text-[#4d071d]">02</span><div><strong>Choose a Service</strong><p class="mt-1 text-sm text-white/65">Pick what you need and your preferred stylist.</p></div></div>
                    <div class="flex gap-4"><span class="step-number flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f4d582] font-bold text-[#4d071d]">03</span><div><strong>Select Date &amp; Time</strong><p class="mt-1 text-sm text-white/65">Choose an available appointment slot.</p></div></div>
                    <div class="flex gap-4"><span class="step-number flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f4d582] font-bold text-[#4d071d]">04</span><div><strong>Instant Confirmation</strong><p class="mt-1 text-sm text-white/65">A valid available appointment is confirmed automatically.</p></div></div>
                </div>
            </div>
        </section>
        @endif

        @if($activeSection === 'contact')
        <section id="contact" class="bg-[#330510] px-5 pb-16 pt-[112px] text-white lg:px-8">
            <div class="mx-auto max-w-3xl rounded-2xl border border-white/15 bg-white/10 p-7 sm:p-10">
                <p class="text-sm font-bold uppercase tracking-[.2em] text-[#f4d582]">Contact</p>
                <h2 class="mt-2 text-4xl">Visit our salon</h2>
                <div class="mt-7 space-y-5 text-sm text-white/80">
                    <p class="flex gap-3"><i data-lucide="map-pin" class="h-5 w-5 shrink-0 text-[#f4d582]"></i><span>{{ $settings?->address ?? 'Poblacion Public Market, San Juan, Batangas' }}</span></p>
                    <p class="flex gap-3"><i data-lucide="clock-3" class="h-5 w-5 shrink-0 text-[#f4d582]"></i><span><strong class="text-white">Weekdays:</strong> {{ date('g:i A', strtotime($settings?->opening_time ?? '10:00')) }}–{{ date('g:i A', strtotime($settings?->closing_time ?? '16:00')) }}<br><strong class="text-white">Weekends:</strong> {{ date('g:i A', strtotime($settings?->weekend_opening_time ?? '09:00')) }}–{{ date('g:i A', strtotime($settings?->weekend_closing_time ?? '17:00')) }}</span></p>
                    <p class="flex flex-wrap gap-3"><i data-lucide="phone" class="h-5 w-5 shrink-0 text-[#f4d582]"></i><span><a href="tel:{{ preg_replace('/[^0-9+]/', '', $settings?->contact_phone ?? '09611556557') }}" class="hover:text-white">{{ $settings?->contact_phone ?? '09611556557' }}</a><br><a href="tel:{{ preg_replace('/[^0-9+]/', '', $settings?->contact_phone_secondary ?? '09192001649') }}" class="hover:text-white">{{ $settings?->contact_phone_secondary ?? '09192001649' }}</a></span></p>
                    <p class="flex gap-3"><i data-lucide="mail" class="h-5 w-5 shrink-0 text-[#f4d582]"></i><a href="mailto:{{ $settings?->contact_email ?? 'dcsisters@yahoo.com' }}" class="hover:text-white">{{ $settings?->contact_email ?? 'dcsisters@yahoo.com' }}</a></p>
                </div>
                <a href="{{ route('register') }}" class="mt-8 inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-[#f4d582] font-bold text-[#4d071d] transition hover:bg-white">Book Your Visit</a>
            </div>
        </section>
        @endif

        @if($activeSection === 'home')
            <section class="bg-[#4d071d] px-5 py-10 text-center text-white lg:px-8"><h2 class="text-3xl">Ready for your next appointment?</h2><p class="mt-2 text-sm text-white/70">Create your account and experience beauty and wellness made easy.</p><a href="{{ route('register', ['return' => 'home']) }}" class="action-link mt-5 inline-flex min-h-11 items-center gap-2 rounded-full bg-[#f4d582] px-7 font-bold text-[#4d071d] hover:bg-white"><i data-lucide="calendar-days" class="h-5 w-5"></i> Book Now</a></section>
        @endif
    </main>
    <footer class="bg-[#26030f] px-5 py-5 text-center text-xs text-white/50">&copy; {{ date('Y') }} {{ $settings?->salon_name ?? "Purita's Beauty Lounge" }}. All rights reserved.</footer>
    <script>
        const mobileMenuButton = document.querySelector('.mobile-menu');
        const mobileNav = document.getElementById('mobileNav');
        mobileMenuButton?.addEventListener('click', () => {
            const isOpen = mobileNav.classList.toggle('hidden') === false;
            mobileMenuButton.setAttribute('aria-expanded', String(isOpen));
        });

        document.querySelectorAll('.action-link').forEach((link) => {
            link.addEventListener('click', () => {
                link.setAttribute('aria-busy', 'true');
                link.classList.add('opacity-75');
            });
        });
    </script>
</body>
</html>
