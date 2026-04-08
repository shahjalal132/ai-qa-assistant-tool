<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload CSV') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-4">
                    {{ __('Required columns: english_url (or english) and welsh_url (or welsh). Other columns are stored as metadata JSON per row.') }}
                </p>
                <form action="{{ route('csv-upload-batches.store') }}" method="post" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="csv" :value="__('CSV file')" />
                        <input id="csv" name="csv" type="file" accept=".csv,.txt,text/csv" required
                            class="mt-1 block w-full text-sm text-gray-600" />
                        <x-input-error :messages="$errors->get('csv')" class="mt-2" />
                    </div>
                    <div class="flex gap-2">
                        <x-primary-button>{{ __('Import') }}</x-primary-button>
                        <a href="{{ route('csv-upload-batches.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
