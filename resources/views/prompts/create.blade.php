<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New prompt') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="post" action="{{ route('prompts.store') }}" class="space-y-6">
                    @csrf
                    @include('prompts._form', ['prompt' => $prompt])
                    <div class="flex gap-2">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('prompts.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
