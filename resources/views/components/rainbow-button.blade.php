@props([
    'href' => '#',
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'group relative inline-flex items-center justify-center rounded-xl p-[1px] font-semibold text-white transition-all duration-300 hover:scale-[1.02] focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-300/70',
    ]) }}
>
    <span class="absolute inset-0 rounded-xl bg-[linear-gradient(110deg,#8b5cf6,#3b82f6,#22d3ee,#8b5cf6)] bg-[length:220%_220%] transition-all duration-500 group-hover:bg-[position:100%_0%]"></span>
    <span class="relative inline-flex w-full items-center justify-center rounded-[11px] bg-black/45 px-8 py-3.5 text-sm backdrop-blur-sm">
        {{ $slot }}
    </span>
</a>
