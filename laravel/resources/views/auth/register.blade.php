<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Your Account - Purita's Beauty Lounge</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700;800&display=swap');
        body { font-family: 'DM Sans', sans-serif; }
        h1 { font-family: 'Playfair Display', Georgia, serif; }
        .register-shell { animation: register-rise .55s ease-out both; }
        @keyframes register-rise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .back-link { color: #6b7280; font-size: .85rem; font-weight: 700; }
        .back-link:hover { color: #7A1C49; }
        .form-input:focus { border-color: #7A1C49; box-shadow: 0 0 0 3px rgba(122, 28, 73, .12); outline: none; }
        .password-wrap { position: relative; }
        .password-wrap .form-input { padding-right: 3.25rem; }
        .toggle-password { position: absolute; top: 50%; right: .75rem; display: inline-flex; min-height: 32px; min-width: 32px; align-items: center; justify-content: center; transform: translateY(-50%); border: 0; background: transparent; color: #6b7280; cursor: pointer; }
        .toggle-password:hover { color: #7A1C49; }
        .password-hint { color: #6b7280; font-size: .75rem; line-height: 1.35; }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation: none !important; transition: none !important; } }
    </style>
</head>
<body class="min-h-screen bg-[#fff8f5] flex items-center justify-center p-6">
    <main class="register-shell w-full max-w-xl bg-white rounded-2xl shadow-xl border border-pink-100 p-6 sm:p-8">
        <a href="{{ request('service') ? route('home').'#services' : route('home') }}" class="back-link inline-flex items-center gap-2 mb-6" aria-label="{{ request('service') ? 'Back to services' : 'Back to homepage' }}"><span aria-hidden="true">←</span> {{ request('service') ? 'Back to services' : 'Back to homepage' }}</a>
        <div class="text-center mb-7"><p class="text-xs font-bold uppercase tracking-[.2em] text-[#9b1c58]">Purita's Beauty Lounge</p><h1 class="text-3xl font-bold text-[#7A1C49] mt-2">Create your account</h1><p class="text-gray-600 mt-2">Book services and manage your salon visits online.</p></div>
        @if(request('service'))<div class="mb-5 rounded-xl border border-[#ead8d5] bg-[#fff8f5] p-4 text-sm text-[#5c0e2a]"><strong>You're booking a service.</strong><p class="mt-1 text-gray-600">Create your account first, then choose your stylist, date, and time.</p></div>@endif
        @if($errors->any())<div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-red-700 text-sm font-semibold">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form method="POST" action="{{ route('register.post') }}" class="space-y-4" id="registerForm">@csrf
            <input type="hidden" name="service" value="{{ request('service') }}"><input type="hidden" name="return" value="{{ request('return') }}">
            <div><label class="font-bold text-sm" for="full_name">Full name</label><input id="full_name" name="full_name" value="{{ old('full_name') }}" required class="form-input w-full mt-1"></div>
            <div><label class="font-bold text-sm" for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-input w-full mt-1"></div>
            <div><label class="font-bold text-sm" for="phone">Phone number</label><input id="phone" name="phone" value="{{ old('phone') }}" required class="form-input w-full mt-1"></div>
            <div><label class="font-bold text-sm" for="password">Password</label><div class="password-wrap mt-1"><input id="password" type="password" name="password" required minlength="8" pattern="(?=.*[A-Z])(?=.*[0-9]).{8,}" aria-describedby="passwordHint" class="form-input w-full"><button type="button" class="toggle-password" data-password-target="password" aria-label="Show password" title="Show password"><span aria-hidden="true">&#128065;</span></button></div><p id="passwordHint" class="password-hint mt-1">Use at least 8 characters, including one uppercase letter and one number.</p></div>
            <div><label class="font-bold text-sm" for="password_confirmation">Confirm password</label><div class="password-wrap mt-1"><input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" class="form-input w-full"><button type="button" class="toggle-password" data-password-target="password_confirmation" aria-label="Show password" title="Show password"><span aria-hidden="true">&#128065;</span></button></div></div>
            <button type="submit" id="registerButton" class="btn btn-primary w-full py-3 font-black transition">Create account</button>
        </form>
        <p class="text-center text-sm text-gray-600 mt-5">Already registered? <a class="font-bold text-[#7A1C49]" href="{{ route('login', request()->only(['service', 'return'])) }}">Sign in</a></p>
    </main>
    <script>
        document.getElementById('registerForm').addEventListener('submit', () => {
            const button = document.getElementById('registerButton');
            button.disabled = true;
            button.textContent = 'Creating account...';
            button.classList.add('opacity-75');
        });

        document.querySelectorAll('.toggle-password').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const password = document.getElementById(toggle.dataset.passwordTarget);
                const isHidden = password.type === 'password';
                password.type = isHidden ? 'text' : 'password';
                toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                toggle.setAttribute('title', isHidden ? 'Hide password' : 'Show password');
            });
        });
    </script>
</body>
</html>
