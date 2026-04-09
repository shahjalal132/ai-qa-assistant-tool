<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-900 leading-tight tracking-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- CSV Batches -->
                <div class="bg-white/80 backdrop-blur-md border border-gray-100 p-6 rounded-3xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-[#1abc9c]/10 flex items-center justify-center text-[#16a085] group-hover:scale-110 transition-transform">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Uploads') }}</span>
                    </div>
                    <h3 class="text-3xl font-extrabold text-gray-900">{{ $batches_count }}</h3>
                    <p class="text-xs text-gray-500 mt-1 font-medium">{{ __('Total CSV Batches') }}</p>
                </div>

                <!-- Prompts -->
                <div class="bg-white/80 backdrop-blur-md border border-gray-100 p-6 rounded-3xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-[#16a085]/10 flex items-center justify-center text-[#16a085] group-hover:scale-110 transition-transform">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('AI Config') }}</span>
                    </div>
                    <h3 class="text-3xl font-extrabold text-gray-900">{{ $prompts_count }}</h3>
                    <p class="text-xs text-gray-500 mt-1 font-medium">{{ __('Analysis Prompts') }}</p>
                </div>

                <!-- QA Runs -->
                <div class="bg-white/80 backdrop-blur-md border border-gray-100 p-6 rounded-3xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-10 w-10 rounded-xl bg-[#1abc9c]/10 flex items-center justify-center text-[#16a085] group-hover:scale-110 transition-transform">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Execution') }}</span>
                    </div>
                    <h3 class="text-3xl font-extrabold text-gray-900">{{ $qa_runs_count }}</h3>
                    <p class="text-xs text-gray-500 mt-1 font-medium">{{ __('Active QA Runs') }}</p>
                </div>

                <!-- Results -->
                <div class="bg-white/80 backdrop-blur-md border border-gray-100 p-6 rounded-3xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-12 w-12 rounded-2xl bg-[#16a085]/10 flex items-center justify-center text-[#16a085] group-hover:scale-110 transition-transform">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Insights') }}</span>
                    </div>
                    <h3 class="text-3xl font-extrabold text-gray-900">{{ $results_count }}</h3>
                    <p class="text-xs text-gray-500 mt-1 font-medium">{{ __('Completed Analyses') }}</p>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-2xl sm:rounded-3xl overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('Recent Activity') }}</h3>
                        <p class="text-xs text-gray-500 mt-1 font-medium">{{ __('Monitor the latest QA execution threads.') }}</p>
                    </div>
                    <a href="{{ route('qa-runs.index') }}" class="text-xs font-bold text-[#16a085] hover:text-[#1abc9c] uppercase tracking-widest transition-colors">
                        {{ __('View All Activity') }} →
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-50">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('QA Run') }}</th>
                                <th class="px-8 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Target URL') }}</th>
                                <th class="px-8 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Status') }}</th>
                                <th class="px-8 py-4 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white/50 text-sm">
                            @forelse($latest_runs as $run)
                                <tr class="hover:bg-gray-50/80 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-7 w-7 rounded-lg bg-[#1abc9c]/10 flex items-center justify-center text-[#16a085]">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-900">#{{ $run->id }}</span>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">{{ $run->prompt->title ?? 'Base' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <p class="max-w-[200px] truncate text-gray-500 font-medium" title="{{ $run->reportUrl->english_url ?? '' }}">
                                            {{ $run->reportUrl->english_url ?? '—' }}
                                        </p>
                                    </td>
                                    <td class="px-8 py-5">
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
                                    </td>
                                    <td class="px-8 py-5 text-right whitespace-nowrap">
                                        <span class="text-xs text-gray-400 font-medium">{{ $run->created_at->diffForHumans() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-10 text-center text-gray-400 font-medium">
                                        {{ __('No recent activity to display.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
