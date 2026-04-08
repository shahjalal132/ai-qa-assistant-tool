<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Batch: :name', ['name' => $batch->filename]) }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <p class="text-sm text-gray-600">{{ __(':count URL pair(s).', ['count' => $batch->reportUrls->count()]) }}</p>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">#</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('English URL') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Welsh URL') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($batch->reportUrls as $row)
                            <tr>
                                <td class="px-4 py-2">{{ $row->id }}</td>
                                <td class="px-4 py-2 break-all max-w-xs">{{ $row->english_url }}</td>
                                <td class="px-4 py-2 break-all max-w-xs">{{ $row->welsh_url }}</td>
                                <td class="px-4 py-2">{{ $row->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <a href="{{ route('csv-upload-batches.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">{{ __('← Back to batches') }}</a>
        </div>
    </div>
</x-app-layout>
