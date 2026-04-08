<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Results') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="flex justify-end">
                <a href="{{ route('results.export') }}"><x-secondary-button type="button">{{ __('Export CSV') }}</x-secondary-button></a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">ID</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Prompt') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('English URL') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Created') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($results as $result)
                            <tr>
                                <td class="px-4 py-2">{{ $result->id }}</td>
                                <td class="px-4 py-2">{{ $result->qaRun->prompt->title ?? '—' }}</td>
                                <td class="px-4 py-2 break-all max-w-xs">{{ str($result->qaRun->reportUrl->english_url ?? '')->limit(40) }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $result->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('results.show', $result) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('View') }}</a>
                                    <a href="{{ route('results.download', $result) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Download') }}</a>
                                    <form action="{{ route('results.destroy', $result) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this result?')));">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('No completed results yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-2">{{ $results->links() }}</div>
        </div>
    </div>
</x-app-layout>
