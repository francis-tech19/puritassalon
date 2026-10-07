<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login – Purita's Beauty Lounge</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ─── Google Fonts ─── */
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600;700&display=swap');

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
            background: #1a0a12;
        }

        /* ─── Layout wrapper ─── */
        #login-screen {
            display: flex;
            min-height: 100vh;
            width: 100%;
            position: relative;
        }

        /* ────────────────────────────────────────────
           LEFT PANEL
        ──────────────────────────────────────────── */
        #panel-left {
            width: 46%;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(1.2rem, 2.5vw, 2.5rem) clamp(1.5rem, 3vw, 3rem);
            background: #5c0e2a url('/images/salon-login-background.png') center / cover no-repeat;
            will-change: transform;
            transition: transform 0.9s cubic-bezier(0.77, 0, 0.18, 1);
            box-sizing: border-box;
            animation: driftClarify 0.95s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes driftClarify {
            from {
                transform: translateX(-55px) scale(0.97);
                opacity: 0;
                filter: blur(10px);
            }
            to {
                transform: translateX(0) scale(1);
                opacity: 1;
                filter: blur(0);
            }
        }

        /* Salon photo overlay */
        #panel-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(160deg, rgba(90,14,42,0.12) 0%, rgba(40,5,18,0.38) 100%);
            z-index: 1;
        }

        /* Decorative curved right edge */
        #panel-left::after {
            content: '';
            position: absolute;
            right: clamp(-22px, -2.6vw, -42px);
            top: -5%;
            bottom: -5%;
            width: clamp(48px, 5.5vw, 86px);
            background: transparent;
            border-right: clamp(5px, 0.55vw, 9px) solid #d4af5a;
            border-radius: 0 52% 52% 0 / 0 50% 50% 0;
            z-index: 2;
        }

        .left-content {
            position: relative;
            z-index: 3;
        }

        /* ─── Logo circle ─── */
        .logo-badge {
            width: clamp(64px, 7vw, 105px);
            height: clamp(64px, 7vw, 105px);
            border-radius: 50%;
            border: clamp(1px, 0.2vw, 3px) solid rgba(212,175,90,0.7);
            overflow: hidden;
            background: rgba(0,0,0,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: clamp(0.4rem, 0.8vw, 0.9rem);
            font-size: clamp(1.8rem, 2.8vw, 3rem);
            box-shadow: 0 0 0 clamp(2px, 0.4vw, 6px) rgba(212,175,90,0.15);
            animation: emblemPulse 3s ease-in-out infinite;
        }

        .logo-badge img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @keyframes emblemPulse {
            0%, 100% { box-shadow: 0 0 0 clamp(2px, 0.4vw, 6px) rgba(212,175,90,0.15); }
            50%       { box-shadow: 0 0 0 clamp(2px, 0.4vw, 6px) rgba(212,175,90,0.15), 0 0 0 10px rgba(212,175,90,0.12); }
        }

        /* ─── Feature list stagger ─── */
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: clamp(0.45rem, 0.75vw, 0.8rem);
            opacity: 0;
            animation: fadeInUp 0.5s forwards;
        }
        .feature-item:nth-child(1) { animation-delay: 0.55s; }
        .feature-item:nth-child(2) { animation-delay: 0.7s; }
        .feature-item:nth-child(3) { animation-delay: 0.85s; }
        .feature-item:nth-child(4) { animation-delay: 1.0s; }

        @keyframes fadeInUp {
            from { transform: translateY(16px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        .salon-name {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(1.4rem, 2.2vw, 2.3rem);
            font-weight: 900;
            color: #d4af5a;
            line-height: 1.05;
            letter-spacing: 0.02em;
        }
        .salon-name span {
            display: block;
            font-size: clamp(0.58rem, 0.85vw, 0.9rem);
            font-weight: 700;
            letter-spacing: 0.22em;
            color: #c9a84c;
            margin-top: 0.15rem;
            font-family: 'Inter', sans-serif;
        }

        .divider-gold {
            width: clamp(28px, 3.5vw, 50px);
            height: 2px;
            background: linear-gradient(90deg, transparent, #d4af5a, transparent);
            margin: clamp(0.4rem, 0.6vw, 0.75rem) 0;
        }

        .system-label {
            font-size: clamp(0.5rem, 0.65vw, 0.68rem);
            letter-spacing: 0.22em;
            color: rgba(212,175,90,0.65);
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: clamp(0.8rem, 1.3vw, 1.4rem);
        }

        /* ─── Feature list ─── */
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: clamp(0.5rem, 0.8vw, 0.9rem);
        }


        .feature-icon {
            width: clamp(24px, 2.2vw, 34px);
            height: clamp(24px, 2.2vw, 34px);
            border: 1.5px solid rgba(212,175,90,0.45);
            border-radius: clamp(6px, 0.6vw, 8px);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #d4af5a;
        }

        .feature-icon svg {
            width: clamp(12px, 1.1vw, 16px);
            height: clamp(12px, 1.1vw, 16px);
        }

        .feature-text strong {
            display: block;
            color: #e8c97a;
            font-size: clamp(0.65rem, 0.8vw, 0.84rem);
            font-weight: 700;
            margin-bottom: 0.1rem;
        }
        .feature-text p {
            color: rgba(255,255,255,0.6);
            font-size: clamp(0.58rem, 0.7vw, 0.74rem);
            line-height: 1.35;
        }

        /* ─── Decorative flowers (top-right of left panel) ─── */
        .deco-flower {
            position: absolute;
            top: clamp(0.5rem, 1.2vw, 1.2rem);
            right: clamp(1.5rem, 4vw, 4rem);
            opacity: 0.18;
            font-size: clamp(3rem, 5vw, 5rem);
            line-height: 1;
            z-index: 3;
            pointer-events: none;
            filter: sepia(1) saturate(3) hue-rotate(330deg);
        }

        /* ────────────────────────────────────────────
           RIGHT PANEL
        ──────────────────────────────────────────── */
        #panel-right {
            width: 54%;
            min-height: 100vh;
            background: #fdf8f1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(1rem, 2vw, 2rem) clamp(1rem, 3vw, 3rem) clamp(2rem, 3vw, 3rem);
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
            will-change: transform;
            transition: transform 0.9s cubic-bezier(0.77, 0, 0.18, 1);
            box-sizing: border-box;
        }

        /* Decorative floral bg */
        #panel-right::before {
            content: '🌸';
            position: absolute;
            top: -1rem;
            right: -1rem;
            font-size: clamp(6rem, 9vw, 9rem);
            opacity: 0.065;
            filter: sepia(1) saturate(2) hue-rotate(330deg);
            pointer-events: none;
        }
        #panel-right::after {
            content: '🌿';
            position: absolute;
            bottom: -0.5rem;
            left: clamp(0.5rem, 2vw, 2rem);
            font-size: clamp(4rem, 7vw, 7rem);
            opacity: 0.06;
            pointer-events: none;
        }

        .login-card {
            background: #fff;
            border-radius: clamp(14px, 1.4vw, 22px);
            padding: clamp(1.4rem, 2.5vw, 2.5rem) clamp(1.3rem, 2.2vw, 2.3rem);
            width: 100%;
            max-width: clamp(320px, 30vw, 430px);
            box-shadow: 0 16px 40px rgba(76, 24, 42, 0.12);
            position: relative;
            z-index: 1;
            animation: spotlightRise 0.85s 0.35s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 1.15rem;
        }
        .back-link:hover { color: #5c0e2a; }

        @keyframes spotlightRise {
            0% {
                transform: perspective(700px) rotateX(-12deg) translateY(28px) scale(0.96);
                opacity: 0;
                box-shadow: 0 0 0 rgba(92,14,42,0);
            }
            65% {
                box-shadow: 0 0 45px rgba(92,14,42,0.18);
            }
            100% {
                transform: perspective(700px) rotateX(0deg) translateY(0) scale(1);
                opacity: 1;
                box-shadow: 0 8px 35px rgba(0,0,0,0.08);
            }
        }

        /* ─── Avatar circle ─── */
        .avatar-ring {
            width: clamp(38px, 48px, 54px);
            height: clamp(38px, 48px, 54px);
            border-radius: 50%;
            border: 2px solid #c9a84c;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto clamp(0.5rem, 0.8vw, 0.9rem);
            color: #c9a84c;
        }

        .welcome-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(1.3rem, 1.6vw, 1.75rem);
            font-weight: 900;
            color: #5c0e2a;
            text-align: center;
            margin-bottom: 0.3rem;
        }

        .welcome-sub {
            text-align: center;
            color: #64748b;
            font-size: clamp(0.7rem, 0.78vw, 0.82rem);
            margin-bottom: clamp(0.8rem, 1.2vw, 1.4rem);
            line-height: 1.4;
        }

        /* ─── Field labels & inputs ─── */
        .field-label {
            display: block;
            font-size: clamp(0.68rem, 0.76vw, 0.8rem);
            font-weight: 700;
            color: #334155;
            margin-bottom: 0.3rem;
        }

        .input-wrap {
            position: relative;
            margin-bottom: clamp(0.65rem, 1vw, 1rem);
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .input-wrap input {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: clamp(8px, 10px, 12px);
            padding: 0 clamp(2.2rem, 2.5vw, 2.8rem) 0 clamp(2.4rem, 2.6vw, 2.8rem);
            height: clamp(38px, 42px, 46px);
            font-size: clamp(0.8rem, 0.88vw, 0.92rem);
            color: #0f172a;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .input-wrap input:focus {
            outline: none;
            border-color: #5c0e2a;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(92,14,42,0.1);
        }

        .input-wrap input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .toggle-pass {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }
        .toggle-pass:hover { color: #5c0e2a; }

        /* ─── Checkbox & Forgot ─── */
        .row-remember {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: clamp(0.75rem, 1.1vw, 1.2rem);
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: clamp(0.7rem, 0.78vw, 0.8rem);
            color: #475569;
            cursor: pointer;
            user-select: none;
        }
        .remember-label input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #5c0e2a;
            cursor: pointer;
        }
        .forgot-link {
            font-size: clamp(0.7rem, 0.78vw, 0.8rem);
            color: #5c0e2a;
            font-weight: 700;
            text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* ─── Sign In button ─── */
        .btn-signin {
            width: 100%;
            background: #5c0e2a;
            color: #fff;
            border: none;
            border-radius: clamp(8px, 10px, 12px);
            height: clamp(38px, 42px, 46px);
            font-size: clamp(0.82rem, 0.9vw, 0.92rem);
            font-weight: 700;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            transition: background 0.2s, transform 0.15s;
            letter-spacing: 0.01em;
            margin-bottom: clamp(0.6rem, 0.9vw, 0.9rem);
        }
        .btn-signin:hover { background: #7A1C49; }
        .btn-signin:active { transform: scale(0.98); }
        .btn-signin:disabled { opacity: 0.6; cursor: not-allowed; }

        /* ─── Contact admin link ─── */
        .contact-row {
            text-align: center;
            font-size: clamp(0.72rem, 0.78vw, 0.8rem);
            color: #888;
        }
        .contact-row a {
            color: #5c0e2a;
            font-weight: 700;
            text-decoration: none;
        }
        .contact-row a:hover { text-decoration: underline; }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* ─── Error alert ─── */
        .error-alert {
            background: #fff0f3;
            border: 1.5px solid #f4a0b0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            color: #9b1c3a;
            font-size: 0.83rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            animation: shake 0.4s ease-in-out;
        }

            .success-alert {
                background: #ecfdf5;
                border: 1.5px solid #86efac;
                border-radius: 10px;
                padding: 0.75rem 1rem;
                color: #166534;
                font-size: 0.83rem;
                font-weight: 700;
                margin-bottom: 1.2rem;
                animation: successRise 0.35s ease-out;
            }

            @keyframes successRise {
                from { opacity: 0; transform: translateY(8px); }
                to { opacity: 1; transform: translateY(0); }
            }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-8px); }
            40%       { transform: translateX(8px); }
            60%       { transform: translateX(-5px); }
            80%       { transform: translateX(5px); }
        }

        /* ─── Footer ─── */
        .page-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.75rem;
            color: #bbb;
            z-index: 1;
            width: 100%;
        }
        .page-footer .secure-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            margin-bottom: 0.2rem;
        }

        /* ─── Spinner (loading state) ─── */
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ─── SUCCESS SPLIT ANIMATION ─── */
        body.splitting #panel-left  { transform: translateX(-110%); }
        body.splitting #panel-right { transform: translateX(110%); }

        /* ─── Responsive & Cross-Device ─── */
        @media (max-width: 1024px) {
            #panel-left {
                width: 45%;
                padding: 1.5rem 2rem;
            }
            #panel-right {
                width: 55%;
                padding: 1.5rem 2rem;
            }
        }

        @media (max-width: 850px) {
            #login-screen {
                flex-direction: column;
                min-height: 100vh;
            }
            #panel-left {
                width: 100%;
                min-height: auto;
                padding: 2.5rem 1.8rem 2rem;
                justify-content: center;
                align-items: center;
                text-align: center;
            }
            #panel-left::after {
                display: none;
            }
            .left-content {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .feature-list {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.8rem;
                text-align: left;
                width: 100%;
                max-width: 520px;
                margin-top: 0.5rem;
            }
            #panel-right {
                width: 100%;
                min-height: auto;
                padding: 2.2rem 1.5rem 3rem;
            }
            .login-card {
                max-width: 440px;
            }
        }

        @media (max-width: 540px) {
            #panel-left {
                padding: 1.25rem 1.2rem 1rem;
            }
            .logo-badge {
                width: 76px;
                height: 76px;
                margin-bottom: 0.45rem;
            }
            .system-label,
            .feature-list {
                display: none;
            }
            .divider-gold {
                margin: 0.35rem 0;
            }
            .login-card {
                padding: 1.4rem 1.2rem;
            }
        }
    </style>
</head>
<body>
<div id="login-screen">

    <!-- ═══════════════════════════════ LEFT PANEL ═══════════════════════════════ -->
    <div id="panel-left">
        <div class="deco-flower">🌸</div>

        <div class="left-content">
            <!-- Logo badge -->
            <div class="logo-badge">
                <img src="{{ asset('images/salon-logo.png') }}" alt="Purita's Beauty Lounge logo">
            </div>

            <!-- Name & tagline -->
            <div class="salon-name">
                PURITA'S
                <span>BEAUTY LOUNGE</span>
            </div>
            <div class="divider-gold"></div>
            <p class="system-label">Your salon visit, made simple</p>

            <!-- Feature list -->
            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="feature-text">
                        <strong>Book Your Visit</strong>
                        <p>Choose a service, stylist, date, and time in a few easy steps.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    </div>
                    <div class="feature-text">
                        <strong>Keep Your History</strong>
                        <p>See your upcoming visits and past appointments in one place.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <div class="feature-text">
                        <strong>Enjoy Rewards</strong>
                        <p>Stay connected to loyalty benefits from your salon visits.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <div class="feature-text">
                        <strong>Easy Online Requests</strong>
                        <p>Send an appointment request whenever it is convenient for you.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════ RIGHT PANEL ═══════════════════════════════ -->
    <div id="panel-right">

        <div class="login-card">

            <a href="{{ request('service') ? route('home').'#services' : route('home') }}" class="back-link" aria-label="{{ request('service') ? 'Back to services' : 'Back to homepage' }}">
                <span aria-hidden="true">←</span> {{ request('service') ? 'Back to services' : 'Back to homepage' }}
            </a>

            <!-- Avatar ring -->
            <div class="avatar-ring">
                <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>

            <h1 class="welcome-title">Welcome Back!</h1>
            <p class="welcome-sub">Sign in to manage your salon or book your next visit.</p>

            <!-- Error messages -->
            @if($errors->any())
                <div class="error-alert">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if(session('success'))
                <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:0.75rem 1rem;color:#166534;font-size:0.83rem;font-weight:600;margin-bottom:1.2rem;">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Login Form -->
            <form id="loginForm" method="POST" action="{{ route('login.post') }}" novalidate>
                @csrf
                <input type="hidden" name="service" value="{{ request('service') }}">
                <input type="hidden" name="return" value="{{ request('return') }}">

                <!-- Username or Email -->
                <label class="field-label" for="username">Username or Email</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        placeholder="Enter your username or email"
                        autocomplete="username"
                    >
                </div>

                <!-- Password -->
                <label class="field-label" for="password">Password</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                    <button type="button" class="toggle-pass" onclick="togglePass()" title="Show/Hide Password">
                        <svg id="eye-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>

                <!-- Remember + Forgot -->
                <div class="row-remember">
                    <label class="remember-label">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="mailto:admin@puritasalon.com?subject=Password%20Reset%20Request" class="forgot-link">Forgot password?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-signin" id="signinBtn">
                    <span id="btn-text">Sign In →</span>
                    <span class="spinner" id="btn-spinner"></span>
                </button>

                <!-- Contact admin -->
                <div class="contact-row">
                    Need access or a password reset? <a href="mailto:admin@puritasalon.com">Contact the system administrator.</a>
                </div>
            </form>

            <p style="text-align:center;margin-top:1rem;font-size:.85rem;color:#6b7280;">
                New customer? <a href="{{ route('register', request()->only(['service', 'return'])) }}" style="color:#7A1C49;font-weight:700;">Create an account</a>
            </p>

        </div>

        <!-- Page footer -->
        <div class="page-footer">
            <div class="secure-row">
                <svg width="13" height="13" fill="none" stroke="#bbb" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Your data is secure with us.
            </div>
            &copy; {{ date('Y') }} Purita's Beauty Lounge. All rights reserved.
        </div>
    </div>

</div><!-- /#login-screen -->

<script>
    /* ─── Toggle Password Visibility ─── */
    function togglePass() {
        const pwd = document.getElementById('password');
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
    }

    /* ─── AJAX Login with Split Animation ─── */
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn     = document.getElementById('signinBtn');
        const btnText = document.getElementById('btn-text');
        const spinner = document.getElementById('btn-spinner');
        const form    = this;

        // Loading state
        btn.disabled = true;
        btnText.style.display = 'none';
        spinner.style.display = 'block';

        try {
            const formData = new FormData(form);

            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                    const role = data.user?.role?.role_name?.toUpperCase();
                    const message = role === 'OWNER'
                        ? 'Welcome back, Purita. Opening your business dashboard...'
                        : role === 'ADMIN'
                            ? 'Welcome back. Opening the administration center...'
                            : 'Welcome back. Opening today\'s workspace...';

                    btnText.textContent = 'Welcome back';
                    btnText.style.display = '';
                    spinner.style.display = 'none';
                    showSuccess(message);
                    playSplitAndRedirect(data.redirect);
            } else {
                // Show error, restore button
                showError(data.message || 'Invalid username or password.');
                btn.disabled = false;
                btnText.style.display = '';
                spinner.style.display = 'none';
            }

        } catch (err) {
            // Network error — fall back to normal POST
            form.submit();
        }
    });

    function playSplitAndRedirect(redirectUrl) {
        document.body.classList.add('splitting');

            const delay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 700;
        setTimeout(() => {
            window.location.href = redirectUrl;
            }, delay);
        }

        function showSuccess(msg) {
            document.querySelectorAll('.error-alert, .success-alert').forEach(el => el.remove());

            const div = document.createElement('div');
            div.className = 'success-alert';
            div.textContent = msg;
            document.getElementById('loginForm').insertBefore(div, document.getElementById('loginForm').firstChild);
    }

    function showError(msg) {
        // Remove existing error if any
        document.querySelectorAll('.error-alert, .success-alert').forEach(el => el.remove());

        const div = document.createElement('div');
        div.className = 'error-alert';
        div.textContent = msg;

        const form = document.getElementById('loginForm');
        form.insertBefore(div, form.firstChild);

        // Force animation replay on repeated errors
        void div.offsetWidth;
        div.style.animation = 'none';
        requestAnimationFrame(() => {
            div.style.animation = '';
        });
    }
</script>
</body>
</html>
