<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Prompts') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-xl bg-[#1abc9c]/10 border border-[#1abc9c]/20 p-4 text-sm text-[#16a085] flex items-center gap-3 shadow-sm">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ __('Prompt Management') }}</h1>
                    <p class="text-sm text-gray-500 mt-1 font-medium">{{ __('Define and configure system instructions for AI analysis.') }}</p>
                </div>
                <a href="{{ route('prompts.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#16a085] px-6 py-3 text-sm font-bold text-white shadow-lg hover:bg-[#1abc9c] hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-[#16a085]/20">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Define New Prompt') }}
                </a>
            </div>

            <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-xl sm:rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Prompt Title') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($prompts as $prompt)
                                <tr class="hover:bg-gray-50/80 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-lg bg-[#1abc9c]/10 flex items-center justify-center text-[#16a085] mr-3">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold text-gray-900 leading-tight">{{ $prompt->title }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($prompt->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#1abc9c]/10 text-[#16a085]">
                                                <span class="h-1.5 w-1.5 rounded-full bg-[#16a085] mr-1.5"></span>
                                                {{ __('Active') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-gray-300 mr-1.5"></span>
                                                {{ __('Inactive') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3">
                                        <a href="{{ route('prompts.show', $prompt) }}" class="text-[#16a085] hover:text-[#1abc9c] text-sm font-bold transition-colors">{{ __('View') }}</a>
                                        <a href="{{ route('prompts.edit', $prompt) }}" class="text-[#16a085] hover:text-[#1abc9c] text-sm font-bold transition-colors">{{ __('Edit') }}</a>
                                        <form action="{{ route('prompts.destroy', $prompt) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this prompt?')));">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-red-400 hover:text-red-600 text-sm font-bold transition-colors">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-gray-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <p class="text-gray-500 font-bold tracking-tight">{{ __('No prompts defined yet.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="px-2">{{ $prompts->links() }}</div>
        </div>
    </div>
</x-app-layout>
