<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
        @endif

        <style>
            .gradient-text {
                background: linear-gradient(135deg, #a78bfa 0%, #60a5fa 40%, #34d399 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .hero-bg {
                background-image: url('https://cdn.prod.website-files.com/65ba9a1f0a4a7ab901ad8d3e/6696608407e73f8c26e6e422_KI%20Assistent.webp');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }
            .nav-btn {
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }
        </style>
    </head>

    <body class="m-0 p-0 overflow-x-hidden">

        {{-- Hero Section --}}
        <section class="hero-bg relative min-h-screen flex flex-col">

            {{-- Dark Overlay --}}
            <div class="absolute inset-0 bg-black/65 z-0"></div>

            {{-- Navigation --}}
            @if (Route::has('login'))
                <header class="relative z-10 w-full px-6 py-5 flex justify-end">
                    <nav class="flex items-center gap-3">
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="nav-btn inline-block px-5 py-2 text-sm font-medium text-white border border-white/30 rounded-lg bg-white/10 hover:bg-white/20 transition-all duration-200"
                            >
                                Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="nav-btn inline-block px-5 py-2 text-sm font-medium text-white/85 border border-white/20 rounded-lg bg-white/5 hover:bg-white/15 hover:text-white transition-all duration-200"
                            >
                                Log in
                            </a>
                        @endauth
                    </nav>
                </header>
            @endif

            {{-- Hero Content --}}
            <div class="relative z-10 flex-1 flex flex-col items-center justify-center text-center px-6 py-16">

                {{-- Main Heading --}}
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold leading-tight tracking-tight mb-6 max-w-4xl">
                    <span class="gradient-text">AI QA Assistant</span>
                    <br>
                    <span class="text-white">Tool.</span>
                </h1>

                {{-- Subheading --}}
                <p class="text-lg sm:text-xl text-white/60 max-w-xl mb-10 leading-relaxed">
                    Automate your quality assurance workflow with intelligent testing, bug detection, and real-time insights.
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="px-8 py-3.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-blue-500 hover:from-violet-500 hover:to-blue-400 transition-all duration-200 shadow-lg shadow-violet-900/40"
                        >
                            Go to Dashboard →
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="nav-btn px-8 py-3.5 rounded-xl text-sm font-semibold text-white/80 border border-white/25 bg-white/5 hover:bg-white/10 hover:text-white transition-all duration-200"
                        >
                            Sign In
                        </a>
                    @endauth
                </div>

            </div>

            {{-- Bottom fade --}}
            <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-black/50 to-transparent z-0"></div>

        </section>

    </body>
</html>