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
                background-image: url('https://images.pexels.com/photos/20870794/pexels-photo-20870794.jpeg');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }
        </style>
    </head>

    <body class="m-0 p-0 overflow-x-hidden">

        {{-- Hero Section --}}
        <section class="hero-bg relative min-h-screen flex flex-col">

            {{-- Dark Overlay --}}
            <div class="absolute inset-0 bg-black/65 z-0"></div>

            {{-- Hero Content --}}
            <div class="relative z-10 flex-1 flex flex-col items-center justify-center text-center px-6 py-16">

                {{-- Main Heading --}}
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold leading-tight tracking-tight mb-6 max-w-4xl">
                    <span class="gradient-text">AI QA Assistant Tool.</span>
                </h1>

                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    @auth
                        <x-rainbow-button :href="url('/dashboard')">
                            Go to Dashboard →
                        </x-rainbow-button>
                    @else
                        <x-rainbow-button :href="route('login')">
                            Sign In
                        </x-rainbow-button>
                        @if (Route::has('register'))
                            <x-rainbow-button :href="route('register')">
                                Register
                            </x-rainbow-button>
                        @endif
                    @endauth
                </div>

            </div>

            {{-- Bottom fade --}}
            <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-black/50 to-transparent z-0"></div>

        </section>

    </body>
</html>