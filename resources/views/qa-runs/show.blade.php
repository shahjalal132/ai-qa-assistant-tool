<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('QA run #:id', ['id' => $run->id]) }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('qa-runs.index') }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-[#16a085] hover:border-[#1abc9c]/30 transition-all shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ __('Run Details') }} <span class="text-gray-400 ml-1">#{{ $run->id }}</span></h1>
                        <div class="flex items-center gap-2 mt-1">
                             @php
                                $statusColors = [
                                    'pending' => 'bg-gray-100 text-gray-500',
                                    'processing' => 'bg-blue-50 text-blue-600 animate-pulse',
                                    'completed' => 'bg-[#1abc9c]/10 text-[#16a085]',
                                    'failed' => 'bg-red-50 text-red-600',
                                ];
                                $colorClass = $statusColors[$run->status] ?? 'bg-gray-100 text-gray-500';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $colorClass }}">
                                {{ $run->status }}
                            </span>
                            <span class="text-xs text-gray-400 font-medium tracking-tight">Started {{ $run->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-xl sm:rounded-3xl overflow-hidden">
                <div class="p-8 sm:p-10 space-y-8">
                    @if ($run->error_message)
                        <div class="p-4 bg-red-50 border border-red-100 rounded-2xl">
                            <h3 class="text-xs font-bold text-red-700 uppercase tracking-wider mb-1">{{ __('Error Details') }}</h3>
                            <p class="text-sm text-red-600 font-medium leading-relaxed">{{ $run->error_message }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">{{ __('Configuration') }}</h3>
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-[#1abc9c]/10 flex items-center justify-center text-[#16a085]">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $run->prompt->title ?? 'Default Analysis' }}</p>
                                    @if($run->prompt)
                                        <a href="{{ route('prompts.show', $run->prompt) }}" class="text-[10px] font-bold text-[#16a085] hover:text-[#1abc9c] uppercase transition-colors">{{ __('View Prompt') }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">{{ __('Activation Status') }}</h3>
                            <div class="flex items-center gap-3">
                                @if($run->is_active)
                                    <div class="h-10 w-10 rounded-xl bg-[#1abc9c]/10 flex items-center justify-center text-[#16a085]">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ __('Currently Active') }}</span>
                                @else
                                    <div class="h-10 w-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-300">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" /></svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-400">{{ __('Analysis Disabled') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-gray-50">
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">{{ __('Target URLs') }}</h3>
                            <div class="space-y-3">
                                <div class="bg-gray-50/50 rounded-xl p-3 border border-gray-100 flex items-center gap-3 group overflow-hidden">
                                     <span class="flex-none px-2 py-1 rounded-md bg-white border border-gray-200 text-[10px] font-bold text-gray-400 uppercase tracking-wider">EN</span>
                                     <p class="text-sm font-medium text-gray-600 truncate flex-1" title="{{ $run->reportUrl->english_url ?? '' }}">{{ $run->reportUrl->english_url ?? '—' }}</p>
                                     <a href="{{ $run->reportUrl->english_url ?? '#' }}" target="_blank" class="text-gray-300 hover:text-[#16a085] transition-colors">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                     </a>
                                </div>
                                <div class="bg-gray-50/50 rounded-xl p-3 border border-gray-100 flex items-center gap-3 group overflow-hidden">
                                     <span class="flex-none px-2 py-1 rounded-md bg-white border border-gray-200 text-[10px] font-bold text-gray-400 uppercase tracking-wider">CY</span>
                                     <p class="text-sm font-medium text-gray-600 truncate flex-1" title="{{ $run->reportUrl->welsh_url ?? '' }}">{{ $run->reportUrl->welsh_url ?? '—' }}</p>
                                      <a href="{{ $run->reportUrl->welsh_url ?? '#' }}" target="_blank" class="text-gray-300 hover:text-[#16a085] transition-colors">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                     </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 pt-8 border-t border-gray-50">
                        <form action="{{ route('qa-runs.toggle', $run) }}" method="post" class="flex-1 min-w-[150px]">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white border border-gray-200 text-gray-600 font-bold text-sm hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                {{ __('Toggle Active') }}
                            </button>
                        </form>
                        
                        <form action="{{ route('qa-runs.retry', $run) }}" method="post" class="flex-1 min-w-[150px]">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white border border-gray-200 text-gray-600 font-bold text-sm hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                {{ __('Re-run Analysis') }}
                            </button>
                        </form>

                        @if ($run->result)
                            <a href="{{ route('results.show', $run->result) }}" class="flex-[2] min-w-[200px] inline-flex items-center justify-center px-6 py-3 rounded-xl bg-[#16a085] text-white font-bold text-sm shadow-lg hover:bg-[#1abc9c] hover:-translate-y-0.5 transition-all">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ __('View Analysis Results') }}
                            </a>
                        @endif
                    </div>
                    
                    @if($run->result)
                         <div class="flex justify-center text-center mt-4">
                            <a href="{{ route('results.download', $run->result) }}" class="inline-flex items-center text-gray-400 hover:text-[#16a085] transition-colors font-bold text-xs uppercase tracking-widest gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                {{ __('Export Raw JSON Result') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
