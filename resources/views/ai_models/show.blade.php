<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight font-mono">{{ $aiModel->name }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('models.index') }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-[#16a085] hover:border-[#1abc9c]/30 transition-all shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight font-mono">{{ $aiModel->name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            @if ($aiModel->is_default)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#1abc9c]/10 text-[#16a085] uppercase tracking-wider">
                                    {{ __('Default') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-400 uppercase tracking-wider">
                                    {{ __('Not default') }}
                                </span>
                            @endif
                            <span class="text-xs text-gray-400 font-medium">{{ __('Updated') }} {{ $aiModel->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('models.edit', $aiModel) }}" class="inline-flex items-center justify-center rounded-xl bg-[#16a085] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1abc9c] transition-all">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        {{ __('Edit') }}
                    </a>
                </div>
            </div>

            @if ($aiModel->note)
                <div class="bg-white border border-gray-100 shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                        <h3 class="text-sm font-bold text-[#16a085] uppercase tracking-wider">{{ __('Note') }}</h3>
                        <div class="h-2 w-2 rounded-full bg-[#1abc9c]"></div>
                    </div>
                    <div class="p-6 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $aiModel->note }}</div>
                </div>
            @else
                <p class="text-sm text-gray-400 font-medium px-1">{{ __('No note.') }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
