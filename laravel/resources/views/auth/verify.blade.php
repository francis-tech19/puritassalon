<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email - Purita's Beauty Lounge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FFF5F8] flex items-center justify-center p-4 sm:p-6 font-sans">
    <main class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-pink-100 p-6 sm:p-8 text-center relative overflow-hidden">
        {{-- Decorative accent top bar --}}
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[#7A1C49] via-pink-600 to-[#D97706]"></div>

        <div class="mb-5 flex justify-center">
            <div class="w-16 h-16 rounded-2xl bg-pink-50 border border-pink-200 flex items-center justify-center shadow-inner">
                <span class="text-3xl font-black font-serif text-[#7A1C49]">P</span>
            </div>
        </div>

        <h1 class="text-2xl sm:text-3xl font-black text-[#7A1C49] tracking-tight">Verify Your Email</h1>
        <p class="text-gray-600 text-sm mt-2 mb-6">
            We sent a six-digit verification code to <br>
            <strong class="text-gray-900 font-bold break-all">{{ $email ?? $user?->email }}</strong>
        </p>

        {{-- Flash Success Message --}}
        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm font-bold flex items-center gap-2 text-left">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Flash Error Message --}}
        @if($errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-300 text-red-800 text-sm font-bold flex items-center gap-2 text-left">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Helper Box for Local Environment or Log Mailer --}}
        @php
            $displayCode = session('demo_verification_code') ?? (app()->environment('local') ? (session('pending_registration.code') ?? $user?->verification_code) : null);
        @endphp
        @if($displayCode)
            <div class="mb-5 p-3.5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs text-left">
                <div class="font-bold flex items-center gap-1.5 mb-1 text-[#D97706]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Verification Code Notice</span>
                </div>
                <p class="text-gray-700">Your 6-digit verification code is: <strong class="text-[#7A1C49] font-black text-base tracking-wider">{{ $displayCode }}</strong></p>
                <p class="text-gray-500 text-[11px] mt-0.5">(Shown for testing/development delivery)</p>
            </div>
        @endif

        {{-- Verification Form --}}
        <form method="POST" action="{{ route('verification.verify', $user ?? '') }}" class="space-y-4">
            @csrf
            <div>
                <label for="verification-code-input" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Enter 6-Digit Code</label>
                <input id="verification-code-input"
                       type="text"
                       name="code"
                       inputmode="numeric"
                       maxlength="6"
                       required
                       autofocus
                       autocomplete="one-time-code"
                       value="{{ old('code', $displayCode) }}"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)"
                       class="form-input w-full text-center text-3xl font-black tracking-[0.4em] py-3.5 rounded-2xl border-2 border-pink-200 focus:border-[#7A1C49] focus:ring focus:ring-[#7A1C49]/20 transition"
                       placeholder="000000">
            </div>

            <button type="submit" class="btn btn-primary w-full py-3.5 rounded-xl font-black text-base shadow-lg shadow-pink-900/10 hover:shadow-pink-900/20 transition">
                Verify Account
            </button>
        </form>

        {{-- Resend Code Section --}}
        <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col gap-3">
            <form method="POST" action="{{ route('verification.resend', $user ?? '') }}">
                @csrf
                <p class="text-xs text-gray-500 mb-2">Didn't receive the email or code expired?</p>
                <button type="submit" class="text-sm font-bold text-[#7A1C49] hover:text-[#5C1236] hover:underline transition">
                    Resend Code
                </button>
            </form>

            <a href="{{ route('login') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-800 transition">
                &larr; Back to Login
            </a>
        </div>
    </main>
</body>
</html>
