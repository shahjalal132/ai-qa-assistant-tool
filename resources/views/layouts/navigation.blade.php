<aside
    class="fixed inset-y-0 start-0 z-40 flex min-h-screen w-64 shrink-0 flex-col bg-[#16a085] text-white shadow-lg transition-transform duration-200 ease-out md:static md:translate-x-0"
    :class="sidebarOpen ? 'max-md:translate-x-0' : 'max-md:-translate-x-full'"
>
    <div class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-white/15 px-4">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center" @click="sidebarOpen = false">
            <x-application-logo class="block h-9 w-auto fill-current text-white" />
        </a>
        <button
            type="button"
            class="inline-flex items-center justify-center rounded-md p-2 text-white/80 hover:bg-white/10 hover:text-white focus:outline-none md:hidden"
            @click="sidebarOpen = false"
            aria-label="{{ __('Close navigation') }}"
        >
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex flex-1 flex-col gap-1 overflow-y-auto py-4 px-2">
        <x-nav-link variant="sidebar" :href="route('dashboard')" :active="request()->routeIs('dashboard')" @click="sidebarOpen = false">
            {{ __('Dashboard') }}
        </x-nav-link>
        <x-nav-link variant="sidebar" :href="route('csv-upload-batches.index')" :active="request()->routeIs('csv-upload-batches.*')" @click="sidebarOpen = false">
            {{ __('CSV uploads') }}
        </x-nav-link>
        <x-nav-link variant="sidebar" :href="route('prompts.index')" :active="request()->routeIs('prompts.*')" @click="sidebarOpen = false">
            {{ __('Prompts') }}
        </x-nav-link>
        <x-nav-link variant="sidebar" :href="route('qa-runs.index')" :active="request()->routeIs('qa-runs.*')" @click="sidebarOpen = false">
            {{ __('QA runs') }}
        </x-nav-link>
        <x-nav-link variant="sidebar" :href="route('results.index')" :active="request()->routeIs('results.*')" @click="sidebarOpen = false">
            {{ __('Results') }}
        </x-nav-link>
        <x-nav-link variant="sidebar" :href="route('settings.index')" :active="request()->routeIs('settings.*')" @click="sidebarOpen = false">
            {{ __('Settings') }}
        </x-nav-link>
    </nav>

    <div class="shrink-0 border-t border-white/15 p-3">
        <x-dropdown align="right" width="48" :dropup="true">
            <x-slot name="trigger">
                <button type="button" class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-start text-sm font-medium text-white/90 transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/20">
                    <span class="truncate">{{ Auth::user()->name }}</span>
                    <svg class="h-4 w-4 shrink-0 fill-current text-white/70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</aside>
