<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('QA run #:id', ['id' => $run->id]) }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-3">
                <p><span class="font-medium text-gray-700">{{ __('Status') }}:</span> {{ $run->status }}</p>
                <p><span class="font-medium text-gray-700">{{ __('Active') }}:</span> {{ $run->is_active ? __('Yes') : __('No') }}</p>
                @if ($run->error_message)
                    <p class="text-red-700"><span class="font-medium">{{ __('Error') }}:</span> {{ $run->error_message }}</p>
                @endif
                <p><span class="font-medium text-gray-700">{{ __('Prompt') }}:</span> {{ $run->prompt->title ?? '—' }}</p>
                <p class="break-all"><span class="font-medium text-gray-700">{{ __('English') }}:</span> {{ $run->reportUrl->english_url ?? '' }}</p>
                <p class="break-all"><span class="font-medium text-gray-700">{{ __('Welsh') }}:</span> {{ $run->reportUrl->welsh_url ?? '' }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <form action="{{ route('qa-runs.toggle', $run) }}" method="post">
                    @csrf
                    <x-secondary-button type="submit">{{ __('Toggle active') }}</x-secondary-button>
                </form>
                <form action="{{ route('qa-runs.retry', $run) }}" method="post">
                    @csrf
                    <x-secondary-button type="submit">{{ __('Retry / re-queue') }}</x-secondary-button>
                </form>
                @if ($run->result)
                    <a href="{{ route('results.show', $run->result) }}"><x-primary-button type="button">{{ __('View result') }}</x-primary-button></a>
                    <a href="{{ route('results.download', $run->result) }}"><x-secondary-button type="button">{{ __('Download JSON') }}</x-secondary-button></a>
                @endif
            </div>

            <a href="{{ route('qa-runs.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">{{ __('← Back to runs') }}</a>
        </div>
    </div>
</x-app-layout>
