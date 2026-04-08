<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $prompt->title }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-2 text-sm">
                <p><span class="font-medium text-gray-700">{{ __('Active') }}:</span> {{ $prompt->is_active ? __('Yes') : __('No') }}</p>
                <div>
                    <p class="font-medium text-gray-700">{{ __('System instruction') }}</p>
                    <pre class="mt-2 p-3 bg-gray-50 rounded text-xs whitespace-pre-wrap overflow-x-auto">{{ $prompt->system_instruction }}</pre>
                </div>
                @if ($prompt->response_schema)
                    <div>
                        <p class="font-medium text-gray-700">{{ __('Response schema') }}</p>
                        <pre class="mt-2 p-3 bg-gray-50 rounded text-xs overflow-x-auto">{{ json_encode($prompt->response_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="{{ route('prompts.edit', $prompt) }}"><x-primary-button type="button">{{ __('Edit') }}</x-primary-button></a>
                <a href="{{ route('prompts.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm self-center">{{ __('← Back') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
