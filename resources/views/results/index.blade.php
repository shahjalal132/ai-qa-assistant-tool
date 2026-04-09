<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Results') }}</h2>
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

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ __('Analysis Results') }}</h1>
                    <p class="text-sm text-gray-500 mt-1 font-medium">{{ __('Access and export the data generated from your AI quality assurance runs.') }}</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="{{ route('results.export') }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-gray-200 px-6 py-3 text-sm font-bold text-[#16a085] shadow-sm hover:bg-gray-50 hover:border-[#1abc9c]/30 transition-all duration-200">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ __('Export Batch CSV') }}
                    </a>
                </div>
            </div>

            <form id="bulk-actions-form" method="POST">
                @csrf
                <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-xl sm:rounded-2xl overflow-hidden relative">
                    <!-- Bulk Action Bar -->
                    <div id="bulk-action-bar" class="hidden absolute top-0 left-0 right-0 z-10 bg-red-600/95 backdrop-blur-xl border-b border-white/20 px-8 py-3 flex items-center justify-between animate-in slide-in-from-top duration-300">
                        <div class="flex items-center gap-4">
                            <span class="text-xs font-bold text-white uppercase tracking-widest">
                                <span id="selected-count">0</span> {{ __('Selected') }}
                            </span>
                            <div class="h-4 w-px bg-white/20"></div>
                            <button type="button" onclick="submitBulk('{{ route('results.bulk-export') }}', 'export')" class="px-4 py-1.5 rounded-lg bg-[#1abc9c] hover:bg-[#16a085] text-white text-[10px] font-bold uppercase tracking-wider transition-all">
                                {{ __('Export Selected') }}
                            </button>
                            <button type="button" onclick="submitBulk('{{ route('results.bulk-destroy') }}', 'delete')" class="px-4 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-[10px] font-bold uppercase tracking-wider transition-all">
                                {{ __('Delete Selected') }}
                            </button>
                        </div>
                        <button type="button" onclick="deselectAll()" class="text-white/60 hover:text-white transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-4 text-left w-10">
                                        <input type="checkbox" id="select-all" class="rounded border-gray-300 text-[#16a085] focus:ring-[#16a085]/20 h-4 w-4 transition-all cursor-pointer">
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider w-16">ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Source Analysis') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Target URL') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Generated') }}</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                @forelse ($results as $result)
                                    <tr class="hover:bg-gray-50/80 transition-colors group">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" name="ids[]" value="{{ $result->id }}" class="row-checkbox rounded border-gray-300 text-[#16a085] focus:ring-[#16a085]/20 h-4 w-4 transition-all cursor-pointer">
                                        </td>
                                        <td class="px-6 py-4 text-gray-400 font-bold">#{{ $result->id }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-lg bg-[#1abc9c]/10 flex items-center justify-center text-[#16a085] mr-3">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <span class="font-bold text-gray-900">{{ $result->qaRun->prompt->title ?? 'Base Analysis' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="max-w-xs truncate text-gray-500 font-medium" title="{{ $result->qaRun->reportUrl->english_url ?? '' }}">
                                                {{ $result->qaRun->reportUrl->english_url ?? '—' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-400 font-medium whitespace-nowrap">{{ $result->created_at->diffForHumans() }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-4">
                                                <a href="{{ route('results.show', $result) }}" class="text-[#16a085] hover:text-[#1abc9c] font-bold transition-colors">{{ __('View Result') }}</a>
                                                
                                                <a href="{{ route('results.download', $result) }}" class="text-gray-400 hover:text-[#16a085] transition-colors" title="{{ __('Download Raw JSON') }}">
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                </a>
    
                                                <button type="button" 
                                                    onclick="if(confirm(@json(__('Delete this result permanently?')))) { document.getElementById('delete-form-{{ $result->id }}').submit(); }"
                                                    class="text-red-300 hover:text-red-500 transition-colors"
                                                >
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="h-12 w-12 text-gray-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2a4 4 0 00-4-4H5m11 0h.01M19 21l-7-7 7-7" />
                                                </svg>
                                                <p class="font-bold tracking-tight">{{ __('No analysis results available yet.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>

            @foreach ($results as $result)
                <form id="delete-form-{{ $result->id }}" action="{{ route('results.destroy', $result) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach

            <script>
                function updateBulkStats() {
                    const checked = document.querySelectorAll('.row-checkbox:checked');
                    const bar = document.getElementById('bulk-action-bar');
                    const count = document.getElementById('selected-count');
                    
                    if (checked.length > 0) {
                        bar.classList.remove('hidden');
                        count.textContent = checked.length;
                    } else {
                        bar.classList.add('hidden');
                    }
                }

                document.getElementById('select-all').addEventListener('change', function(e) {
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    checkboxes.forEach(cb => cb.checked = e.target.checked);
                    updateBulkStats();
                });

                document.querySelectorAll('.row-checkbox').forEach(cb => {
                    cb.addEventListener('change', updateBulkStats);
                });

                function deselectAll() {
                    document.getElementById('select-all').checked = false;
                    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
                    updateBulkStats();
                }

                function submitBulk(url, action) {
                    const msg = action === 'delete' ? '{{ __("Confirm deletion of selected results? This will reset the corresponding QA runs to pending.") }}' : '{{ __("Confirm export of selected results to CSV?") }}';
                    if (confirm(msg)) {
                        const form = document.getElementById('bulk-actions-form');
                        form.action = url;
                        form.submit();
                    }
                }
            </script>
            <div class="px-2">{{ $results->links() }}</div>
        </div>
    </div>
</x-app-layout>
