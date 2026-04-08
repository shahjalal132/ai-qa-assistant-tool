@props(['active', 'variant' => 'default'])

@php
$isSidebar = ($variant ?? 'default') === 'sidebar';

if ($isSidebar) {
    $classes = ($active ?? false)
                ? 'flex items-center px-4 py-2.5 text-sm font-medium text-white bg-white/15 border-s-4 border-teal-300 transition duration-150 ease-in-out'
                : 'flex items-center px-4 py-2.5 text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 border-s-4 border-transparent transition duration-150 ease-in-out';
} else {
    $classes = ($active ?? false)
                ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
                : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
}
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
