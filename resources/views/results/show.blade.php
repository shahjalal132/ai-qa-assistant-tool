<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Result #:id', ['id' => $result->id]) }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('results.index') }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-[#16a085] hover:border-[#1abc9c]/30 transition-all shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ __('Analysis Payload') }} <span class="text-gray-400 ml-1">#{{ $result->id }}</span></h1>
                        <p class="text-xs text-gray-400 font-medium mt-1">Generated for QA Run #{{ $result->qa_run_id }} • {{ $result->created_at->toDayDateTimeString() }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('results.download', $result) }}" class="inline-flex items-center justify-center rounded-xl bg-[#16a085] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1abc9c] transition-all">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ __('Download JSON') }}
                    </a>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-xl sm:rounded-3xl overflow-hidden">
                <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex items-center gap-4">
                    <div class="h-8 w-8 rounded-lg bg-[#1abc9c]/10 flex items-center justify-center text-[#16a085]">
                         <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-bold text-[#16a085] uppercase tracking-widest mb-0.5">{{ __('Target URL') }}</p>
                        <p class="text-sm font-bold text-gray-900 truncate" title="{{ $result->qaRun->reportUrl->english_url ?? '' }}">{{ $result->qaRun->reportUrl->english_url ?? '—' }}</p>
                    </div>
                </div>
                <div class="p-0">
                    <div class="relative group">
                         <div class="absolute top-4 right-4 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="px-2 py-1 rounded bg-[#1abc9c] text-white text-[10px] font-bold uppercase tracking-wider shadow-lg">{{ __('Raw Data Output') }}</span>
                        </div>
                        <pre class="p-8 bg-[#0f172a] text-emerald-400 text-sm font-mono whitespace-pre-wrap leading-relaxed selection:bg-[#1abc9c] selection:text-white max-h-[70vh] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent">{{ json_encode($result->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>

            @php
                $failedProbes = $result->qaRun->linkProbes->filter(fn ($p) => $p->outcome_label !== 'reachable')->values();
            @endphp
            @if ($failedProbes->isNotEmpty())
                @php
                    $probesSorted = $failedProbes->sort(function ($a, $b) {
                        if ($a->page_side !== $b->page_side) {
                            return $a->page_side <=> $b->page_side;
                        }
                        if ($a->is_critical !== $b->is_critical) {
                            return $b->is_critical <=> $a->is_critical;
                        }

                        return $a->url <=> $b->url;
                    })->values();
                @endphp
                <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-xl sm:rounded-3xl overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('Link checks (HEAD, unauthenticated)') }}</h2>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Only non-reachable or error responses are stored. Same as the “Inaccessible links” CSV column. Critical = NHS download button or /app/uploads/ URL.') }}</p>
                    </div>
                    <div class="overflow-x-auto max-h-[50vh] overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50/80 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-[#16a085] uppercase">{{ __('Side') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-[#16a085] uppercase">{{ __('Critical') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-[#16a085] uppercase">{{ __('HTTP') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-[#16a085] uppercase">{{ __('Outcome') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-[#16a085] uppercase">{{ __('URL') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($probesSorted as $probe)
                                    <tr class="bg-red-50/50">
                                        <td class="px-4 py-2 font-mono text-xs text-gray-600">{{ strtoupper($probe->page_side) }}</td>
                                        <td class="px-4 py-2 text-xs">{{ $probe->is_critical ? __('Yes') : __('No') }}</td>
                                        <td class="px-4 py-2 font-mono text-xs">{{ $probe->http_status }}</td>
                                        <td class="px-4 py-2 text-xs font-medium">{{ $probe->outcome_label }}</td>
                                        <td class="px-4 py-2 text-xs break-all text-gray-700"><a href="{{ $probe->url }}" target="_blank" rel="noopener" class="text-[#16a085] hover:underline">{{ $probe->url }}</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
