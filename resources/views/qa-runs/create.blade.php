<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create QA runs') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($batches->isEmpty())
                    <p class="text-sm text-gray-600">{{ __('Upload a CSV batch first.') }}</p>
                    <a href="{{ route('csv-upload-batches.create') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800 text-sm">{{ __('Upload CSV') }}</a>
                @elseif ($prompts->isEmpty())
                    <p class="text-sm text-gray-600">{{ __('Create an active prompt first.') }}</p>
                    <a href="{{ route('prompts.create') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800 text-sm">{{ __('New prompt') }}</a>
                @else
                    <form method="post" action="{{ route('qa-runs.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="csv_upload_batch_id" :value="__('CSV batch')" />
                            <select id="csv_upload_batch_id" name="csv_upload_batch_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                @foreach ($batches as $b)
                                    <option value="{{ $b->id }}">{{ $b->filename }} ({{ $b->report_urls_count }} {{ __('rows') }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('csv_upload_batch_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="prompt_id" :value="__('Prompt')" />
                            <select id="prompt_id" name="prompt_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                @foreach ($prompts as $p)
                                    <option value="{{ $p->id }}">{{ $p->title }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('prompt_id')" class="mt-2" />
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="dispatch" value="0" />
                            <input id="dispatch" name="dispatch" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" checked />
                            <x-input-label for="dispatch" :value="__('Dispatch queue jobs for pending runs')" />
                        </div>
                        <div class="flex gap-2">
                            <x-primary-button>{{ __('Create / link runs') }}</x-primary-button>
                            <a href="{{ route('qa-runs.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
