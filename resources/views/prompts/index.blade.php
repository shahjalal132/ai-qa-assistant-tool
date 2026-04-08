<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Prompts') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="flex justify-end">
                <a href="{{ route('prompts.create') }}"><x-primary-button type="button">{{ __('New prompt') }}</x-primary-button></a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Title') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">{{ __('Active') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($prompts as $prompt)
                            <tr>
                                <td class="px-4 py-2 font-medium">{{ $prompt->title }}</td>
                                <td class="px-4 py-2">{{ $prompt->is_active ? __('Yes') : __('No') }}</td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('prompts.show', $prompt) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('View') }}</a>
                                    <a href="{{ route('prompts.edit', $prompt) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Edit') }}</a>
                                    <form action="{{ route('prompts.destroy', $prompt) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this prompt?')));">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">{{ __('No prompts yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-2">{{ $prompts->links() }}</div>
        </div>
    </div>
</x-app-layout>
