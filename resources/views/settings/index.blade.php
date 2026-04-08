<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Settings') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800 mb-4">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="post" action="{{ route('settings.update') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="gemini_api_key" :value="__('Gemini API key')" />
                        <x-text-input id="gemini_api_key" name="gemini_api_key" type="password" class="mt-1 block w-full" :value="old('gemini_api_key', $gemini_api_key)" autocomplete="off" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Stored in the database. Leave blank to keep clearing only when you submit empty — optional.') }}</p>
                        <x-input-error :messages="$errors->get('gemini_api_key')" class="mt-2" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="use_dummy_ai" value="0" />
                        <input id="use_dummy_ai" name="use_dummy_ai" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm"
                            @checked(old('use_dummy_ai', $use_dummy_ai)) />
                        <x-input-label for="use_dummy_ai" :value="__('Use dummy AI responses (smoke test, no Gemini calls)')" />
                    </div>
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
