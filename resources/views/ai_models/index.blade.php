<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Models') }}</h2>
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

            @if ($errors->has('delete'))
                <div class="rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-600 font-bold flex items-center gap-3 shadow-sm">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    {{ $errors->first('delete') }}
                </div>
            @endif

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ __('Gemini models') }}</h1>
                    <p class="text-sm text-gray-500 mt-1 font-medium">{{ __('Register model ids for QA analysis.') }}</p>
                </div>
                <a href="{{ route('models.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#16a085] px-6 py-3 text-sm font-bold text-white shadow-lg hover:bg-[#1abc9c] hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-[#16a085]/20">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Add model') }}
                </a>
            </div>

            <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-xl sm:rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Model name') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Default') }}</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($models as $m)
                                <tr class="hover:bg-gray-50/80 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-lg bg-[#1abc9c]/10 flex items-center justify-center text-[#16a085] mr-3">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="text-sm font-bold text-gray-900 leading-tight font-mono">{{ $m->name }}</span>
                                                @if ($m->note)
                                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $m->note }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($m->is_default)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#1abc9c]/10 text-[#16a085]">
                                                <span class="h-1.5 w-1.5 rounded-full bg-[#16a085] mr-1.5"></span>
                                                {{ __('Yes') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-400">
                                                {{ __('No') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3">
                                        <a href="{{ route('models.show', $m) }}" class="text-[#16a085] hover:text-[#1abc9c] text-sm font-bold transition-colors">{{ __('View') }}</a>
                                        <a href="{{ route('models.edit', $m) }}" class="text-[#16a085] hover:text-[#1abc9c] text-sm font-bold transition-colors">{{ __('Edit') }}</a>
                                        <form action="{{ route('models.destroy', $m) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this model?')));">
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                            </svg>
                                            <p class="text-gray-500 font-bold tracking-tight">{{ __('No models defined yet.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="px-2">{{ $models->links() }}</div>
        </div>
    </div>
</x-app-layout>
