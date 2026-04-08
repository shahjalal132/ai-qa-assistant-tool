<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex bg-gray-100" x-data="{ sidebarOpen: false }">
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-30 bg-black/40 md:hidden"
                @click="sidebarOpen = false"
                style="display: none;"
            ></div>

            @include('layouts.navigation')

            <div class="flex flex-1 flex-col min-w-0 min-h-screen">
                <div class="md:hidden sticky top-0 z-20 flex items-center gap-3 h-14 px-4 bg-white border-b border-gray-200 shadow-sm">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none"
                        @click="sidebarOpen = true"
                        aria-label="{{ __('Open navigation') }}"
                    >
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="font-semibold text-gray-800 truncate">{{ config('app.name', 'Laravel') }}</a>
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
