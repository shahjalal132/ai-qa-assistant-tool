<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('QA runs') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="flex flex-wrap gap-4 justify-between items-center">
                <form method="get" class="flex items-center gap-2 text-sm">
                    <label for="status" class="text-gray-600">{{ __('Status') }}</label>
                    <select name="status" id="status" class="rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                        <option value="">{{ __('All') }}</option>
                        @foreach (['pending', 'processing', 'completed', 'failed'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('qa-runs.create') }}"><x-primary-button type="button">{{ __('New runs') }}</x-primary-button></a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">ID</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Prompt') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('English URL') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Status') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Active') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($runs as $run)
                            <tr>
                                <td class="px-4 py-2">{{ $run->id }}</td>
                                <td class="px-4 py-2">{{ $run->prompt->title ?? '—' }}</td>
                                <td class="px-4 py-2 break-all max-w-xs">{{ str($run->reportUrl->english_url ?? '')->limit(48) }}</td>
                                <td class="px-4 py-2">{{ $run->status }}</td>
                                <td class="px-4 py-2">{{ $run->is_active ? __('Yes') : __('No') }}</td>
                                <td class="px-4 py-2 text-right space-x-1 whitespace-nowrap">
                                    <a href="{{ route('qa-runs.show', $run) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('View') }}</a>
                                    <form action="{{ route('qa-runs.toggle', $run) }}" method="post" class="inline">
                                        @csrf
                                        <button type="submit" class="text-gray-600 hover:text-gray-900">{{ __('Toggle') }}</button>
                                    </form>
                                    @if ($run->status === 'failed' || $run->status === 'completed')
                                        <form action="{{ route('qa-runs.retry', $run) }}" method="post" class="inline">
                                            @csrf
                                            <button type="submit" class="text-amber-600 hover:text-amber-800">{{ __('Retry') }}</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('qa-runs.destroy', $run) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this run?')));">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No runs yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-2">{{ $runs->links() }}</div>
        </div>
    </div>
</x-app-layout>
