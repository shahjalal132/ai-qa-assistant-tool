<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('CSV uploads') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="flex justify-end">
                <a href="{{ route('csv-upload-batches.create') }}">
                    <x-primary-button type="button">{{ __('Upload CSV') }}</x-primary-button>
                </a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('File') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Rows') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Uploaded') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($batches as $batch)
                            <tr>
                                <td class="px-4 py-2">{{ $batch->filename }}</td>
                                <td class="px-4 py-2">{{ $batch->total_rows }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $batch->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('csv-upload-batches.show', $batch) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('View') }}</a>
                                    <form action="{{ route('csv-upload-batches.destroy', $batch) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this batch and all URLs?')));">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">{{ __('No uploads yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-2">{{ $batches->links() }}</div>
        </div>
    </div>
</x-app-layout>
