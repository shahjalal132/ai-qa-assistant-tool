{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — {{ config('app.name', 'AI QA Assistant') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Instrument Sans', sans-serif;
            background: #f0f0f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .card {
            display: flex;
            width: 100%;
            max-width: 860px;
            min-height: 520px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(153, 144, 250, 0.18), 0 4px 16px rgba(0,0,0,0.08);
        }

        /* ── Left Panel ── */
        .panel-left {
            width: 42%;
            background: linear-gradient(160deg, #1abc9c 0%, #16a085 100%);
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .panel-left::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }
        .panel-left::after {
            content: '';
            position: absolute;
            bottom: -40px; left: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .brand-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(8px);
        }
        .brand-icon svg { width: 18px; height: 18px; }
        .brand-name {
            font-size: 14px;
            font-weight: 600;
            color: rgba(255,255,255,0.9);
            letter-spacing: 0.02em;
        }
        .panel-copy { position: relative; z-index: 1; }
        .panel-copy h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.25;
            margin-bottom: 1rem;
        }
        .panel-copy p {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.72);
            line-height: 1.65;
        }
        .panel-badges {
            display: flex;
            flex-direction: column;
            gap: 10px;
            position: relative;
            z-index: 1;
        }
        .badge {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: rgba(255,255,255,0.8);
        }
        .badge-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            flex-shrink: 0;
        }

        /* ── Right Panel ── */
        .panel-right {
            flex: 1;
            background: #fff;
            padding: 3rem 2.75rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 0.35rem;
        }
        .form-subtitle {
            font-size: 0.875rem;
            color: #888;
            margin-bottom: 2rem;
        }

        /* Session status */
        .session-status {
            background: #edfff4;
            border: 1px solid #6ee7b7;
            color: #065f46;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            margin-bottom: 1.2rem;
        }

        /* Form elements */
        .field { margin-bottom: 1.25rem; }
        .field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 0.45rem;
            letter-spacing: 0.01em;
        }
        .input-wrap { position: relative; }
        .input-wrap input {
            width: 100%;
            height: 46px;
            border: 1.5px solid #e2e2f0;
            border-radius: 10px;
            padding: 0 44px 0 14px;
            font-family: 'Instrument Sans', sans-serif;
            font-size: 0.875rem;
            color: #1a1a2e;
            background: #fafafa;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .input-wrap input:focus {
            border-color: #1abc9c;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.15);
        }
        .input-wrap input.is-error {
            border-color: #f87171;
            box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.12);
        }
        .input-icon {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            color: #16a085;
            display: flex; align-items: center;
            cursor: pointer;
        }
        .input-icon svg { width: 17px; height: 17px; }

        .error-msg {
            font-size: 0.75rem;
            color: #ef4444;
            margin-top: 0.35rem;
        }

        /* Remember + forgot row */
        .row-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 0.8rem;
            color: #555;
            cursor: pointer;
        }
        .remember input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: #16a085;
            cursor: pointer;
            border-radius: 4px;
        }
        .forgot-link {
            font-size: 0.8rem;
            color: #1abc9c;
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* Submit button */
        .btn-login {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #1abc9c, #16a085);
            color: #fff;
            font-family: 'Instrument Sans', sans-serif;
            font-size: 0.925rem;
            font-weight: 600;
            border: none;
            border-radius: 11px;
            cursor: pointer;
            letter-spacing: 0.02em;
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 18px rgba(22, 160, 133, 0.4);
        }
        .btn-login:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            box-shadow: 0 6px 22px rgba(22, 160, 133, 0.5);
        }
        .btn-login:active { transform: translateY(0); }

        .divider {
            text-align: center;
            font-size: 0.75rem;
            color: #ccc;
            margin: 1.25rem 0 0;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 0.75rem;
            font-size: 0.8rem;
            color: #aaa;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover { color: #1abc9c; }

        /* Responsive */
        @media (max-width: 640px) {
            .panel-left { display: none; }
            .panel-right { padding: 2.5rem 1.75rem; }
        }
    </style>
</head>
<body>

    <div class="card">

        {{-- ── Left Panel ── --}}
        <div class="panel-left">
            <div class="brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <span class="brand-name">AI QA Assistant</span>
            </div>

            <div class="panel-copy">
                <h2>Welcome Back</h2>
                <p>Smarter testing starts here — sign in to run AI-powered QA, catch bugs before they ship, and keep your releases confident.</p>
            </div>

            <div class="panel-badges">
                <div class="badge"><span class="badge-dot"></span> Automated test generation</div>
                <div class="badge"><span class="badge-dot"></span> Real-time bug detection</div>
                <div class="badge"><span class="badge-dot"></span> Zero-friction CI/CD integration</div>
            </div>
        </div>

        {{-- ── Right Panel ── --}}
        <div class="panel-right">

            <h1 class="form-title">Sign In</h1>
            <p class="form-subtitle">Enter your credentials to access your account.</p>

            {{-- Session Status --}}
            @if (session('status'))
                <div class="session-status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="field">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="mail@example.com"
                            class="{{ $errors->has('email') ? 'is-error' : '' }}"
                        >
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </span>
                    </div>
                    @error('email')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="{{ $errors->has('password') ? 'is-error' : '' }}"
                        >
                        <span class="input-icon" onclick="togglePassword()" title="Toggle visibility">
                            <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </span>
                    </div>
                    @error('password')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember + Forgot --}}
                <div class="row-meta">
                    <label class="remember">
                        <input type="checkbox" name="remember" id="remember_me" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    @endif
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-login">Log In</button>

                <p class="divider">— or —</p>
                <a href="{{ url('/') }}" class="back-link">← Back to home</a>

            </form>
        </div>

    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('eye-icon');
            const show  = input.type === 'password';
            input.type  = show ? 'text' : 'password';
            icon.innerHTML = show
                ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
                : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    </script>

</body>
</html>