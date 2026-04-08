<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Result #:id', ['id' => $result->id]) }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-2">
                <p><span class="font-medium">{{ __('QA run') }}:</span> #{{ $result->qa_run_id }}</p>
                <p class="break-all"><span class="font-medium">{{ __('English') }}:</span> {{ $result->qaRun->reportUrl->english_url ?? '' }}</p>
            </div>
            <pre class="bg-gray-900 text-green-100 p-4 rounded-lg text-xs overflow-x-auto">{{ json_encode($result->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            <div class="flex gap-2">
                <a href="{{ route('results.download', $result) }}"><x-primary-button type="button">{{ __('Download JSON') }}</x-primary-button></a>
                <a href="{{ route('results.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm self-center">{{ __('← Back') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
