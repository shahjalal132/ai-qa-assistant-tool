<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Batch: :name', ['name' => $batch->filename]) }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-xl bg-[#1abc9c]/10 border border-[#1abc9c]/20 p-4 text-sm text-[#16a085] flex items-center gap-3">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-4">
                <div class="flex items-center gap-4 w-full lg:w-auto">
                    <form method="get" class="flex-1 lg:w-96">
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400 group-focus-within:text-[#16a085] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search URLs...') }}" class="block w-full pl-11 pr-4 py-2.5 bg-white border border-gray-100 rounded-xl text-sm font-medium placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-[#16a085]/10 focus:border-[#16a085] transition-all shadow-sm">
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-4 w-full lg:w-auto">
                    <div class="flex items-center bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-3">{{ __('Total') }}</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-[#1abc9c]/10 text-[#16a085]">
                            {{ __(':count URL pair(s)', ['count' => $reportUrls->total()]) }}
                        </span>
                    </div>
                </div>
            </div>

            <form id="bulk-actions-form" action="{{ route('csv-upload-batches.bulk-action-urls', $batch) }}" method="POST">
                @csrf
                <input type="hidden" name="action" id="bulk-action-input" value="delete">
                
                <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-xl sm:rounded-2xl overflow-hidden relative">
                    <!-- Bulk Action Bar -->
                    <div id="bulk-action-bar" class="hidden absolute top-0 left-0 right-0 z-10 bg-red-600/95 backdrop-blur-xl border-b border-white/20 px-8 py-3 flex items-center justify-between animate-in slide-in-from-top duration-300">
                        <div class="flex items-center gap-4">
                            <span class="text-xs font-bold text-white uppercase tracking-widest">
                                <span id="selected-count">0</span> {{ __('Selected') }}
                            </span>
                            <div class="h-4 w-px bg-white/20"></div>
                            <button type="button" onclick="submitBulk()" class="px-4 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-[10px] font-bold uppercase tracking-wider transition-all">
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
                                    <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider w-16">#</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('URL Pairs') }}</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-[#16a085] uppercase tracking-wider w-32">{{ __('Status') }}</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-[#16a085] uppercase tracking-wider w-24">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($reportUrls as $row)
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" name="ids[]" value="{{ $row->id }}" class="row-checkbox rounded border-gray-300 text-[#16a085] focus:ring-[#16a085]/20 h-4 w-4 transition-all cursor-pointer">
                                        </td>
                                        <td class="px-6 py-4 text-sm font-bold text-gray-400">{{ ($reportUrls->currentPage() - 1) * $reportUrls->perPage() + $loop->iteration }}</td>
                                        <td class="px-6 py-4">
                                            <div class="space-y-1.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[9px] font-black uppercase text-gray-400 bg-gray-100 px-1 rounded">EN</span>
                                                    <div class="text-sm text-gray-700 font-medium break-all">{{ $row->english_url }}</div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[9px] font-black uppercase text-[#16a085] bg-[#1abc9c]/10 px-1 rounded">CY</span>
                                                    <div class="text-sm text-gray-500 font-medium break-all">{{ $row->welsh_url }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-600">
                                                {{ $row->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button type="button" 
                                                onclick="if(confirm('{{ __('Delete this URL pair?') }}')) { document.getElementById('delete-url-{{ $row->id }}').submit(); }"
                                                class="text-gray-300 hover:text-red-500 transition-colors p-1"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                            {{ __('No URL pairs found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>

            @foreach ($reportUrls as $row)
                <form id="delete-url-{{ $row->id }}" action="{{ route('csv-upload-batches.destroy-url', [$batch, $row]) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach

            <div class="mt-6">
                {{ $reportUrls->links() }}
            </div>
            
            <div class="flex justify-center pt-8">
                 <a href="{{ route('csv-upload-batches.index') }}" class="text-xs font-bold text-gray-400 hover:text-[#16a085] uppercase tracking-widest transition-colors flex items-center gap-2">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Back to all batches') }}
                </a>
            </div>

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

                function submitBulk() {
                    if (confirm('{{ __("Are you sure you want to delete the selected URL pairs?") }}')) {
                        document.getElementById('bulk-actions-form').submit();
                    }
                }
            </script>
        </div>
    </div>
</x-app-layout>
