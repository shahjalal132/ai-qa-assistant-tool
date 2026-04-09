<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create QA runs') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-2xl sm:rounded-3xl p-8 sm:p-12">
                <div class="text-center mb-10">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-[#1abc9c]/10 text-[#16a085] mb-4">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Launch QA Analysis') }}</h1>
                    <p class="text-gray-500 mt-3 text-lg font-medium">{{ __('Select a data batch and analysis configuration to start.') }}</p>
                </div>

                @if ($batches->isEmpty())
                    <div class="text-center py-10 bg-gray-50/50 rounded-2xl border-2 border-dashed border-gray-100">
                        <p class="text-sm text-gray-500 font-bold mb-4">{{ __('No source data available.') }}</p>
                        <a href="{{ route('csv-upload-batches.create') }}" class="inline-flex items-center text-[#16a085] hover:text-[#1abc9c] font-bold text-sm transition-colors">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Upload CSV Batch First') }}
                        </a>
                    </div>
                @elseif ($prompts->isEmpty())
                     <div class="text-center py-10 bg-gray-50/50 rounded-2xl border-2 border-dashed border-gray-100">
                        <p class="text-sm text-gray-500 font-bold mb-4">{{ __('No analysis prompts defined.') }}</p>
                        <a href="{{ route('prompts.create') }}" class="inline-flex items-center text-[#16a085] hover:text-[#1abc9c] font-bold text-sm transition-colors">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Define New Prompt') }}
                        </a>
                    </div>
                @else
                    <form
                        method="post"
                        action="{{ route('qa-runs.store') }}"
                        class="space-y-8"
                        x-data="{
                            submitting: false,
                            error: '',
                            mode: 'all', {{-- 'all' or 'selective' --}}
                            selectedBatchId: '{{ $batches->first()->id ?? '' }}',
                            selectedIds: [],
                            modalOpen: false,
                            loadingModal: false,
                            modalData: null,
                            modalPage: 1,
                            modalSearch: '',

                            async submitRuns(e) {
                                e.preventDefault();
                                this.submitting = true;
                                this.error = '';
                                const form = e.target;
                                const fd = new FormData(form);
                                
                                {{-- If selective mode, append the IDs --}}
                                if (this.mode === 'selective') {
                                    this.selectedIds.forEach(id => fd.append('ids[]', id));
                                    if (this.selectedIds.length === 0) {
                                        this.error = '{{ __('Please select at least one record or switch to All Rows mode.') }}';
                                        this.submitting = false;
                                        return;
                                    }
                                }

                                try {
                                    const res = await fetch(form.action, {
                                        method: 'POST',
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                        },
                                        body: fd,
                                    });
                                    const data = await res.json().catch(() => ({}));
                                    if (res.ok && data.redirect) {
                                        window.location.href = data.redirect;
                                        return;
                                    }
                                    this.submitting = false;
                                    if (res.status === 422 && data.errors) {
                                        this.error = Object.values(data.errors).flat().join(' ');
                                    } else {
                                        this.error = data.message || '{{ __('Something went wrong.') }}';
                                    }
                                } catch (err) {
                                    this.submitting = false;
                                    this.error = '{{ __('Network error. Try again.') }}';
                                }
                            },

                            async openModal() {
                                if (!this.selectedBatchId) return;
                                this.modalOpen = true;
                                if (!this.modalData) {
                                    await this.fetchModalData();
                                }
                            },

                            async fetchModalData(page = 1) {
                                this.loadingModal = true;
                                try {
                                    const url = `/csv-upload-batches/${this.selectedBatchId}/items?page=${page}&search=${this.modalSearch}`;
                                    const res = await fetch(url);
                                    this.modalData = await res.json();
                                    this.modalPage = page;
                                } catch (err) {
                                    console.error('Modal fetch failed', err);
                                } finally {
                                    this.loadingModal = false;
                                }
                            },

                            toggleSelection(id) {
                                const idx = this.selectedIds.indexOf(id);
                                if (idx > -1) {
                                    this.selectedIds.splice(idx, 1);
                                } else {
                                    this.selectedIds.push(id);
                                }
                            },

                            toggleAllOnPage() {
                                if (!this.modalData) return;
                                const pageIds = this.modalData.data.map(item => item.id);
                                const allSelected = pageIds.every(id => this.selectedIds.includes(id));
                                
                                if (allSelected) {
                                    this.selectedIds = this.selectedIds.filter(id => !pageIds.includes(id));
                                } else {
                                    pageIds.forEach(id => {
                                        if (!this.selectedIds.includes(id)) {
                                            this.selectedIds.push(id);
                                        }
                                    });
                                }
                            },

                            resetSelection() {
                                this.selectedIds = [];
                                this.modalData = null;
                                this.modalPage = 1;
                                this.modalSearch = '';
                            }
                        }"
                        @submit="submitRuns($event)"
                    >
                        @csrf
                        
                        {{-- Loading Overlay --}}
                        <div x-show="submitting" x-transition.opacity x-cloak class="rounded-2xl bg-[#1abc9c]/5 border border-[#1abc9c]/20 p-6 text-sm text-[#16a085]">
                            <div class="flex items-center gap-4 mb-3">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="font-bold tracking-tight">{{ __('Linking runs and queueing analysis jobs…') }}</p>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-white/50 border border-[#1abc9c]/10">
                                <div class="h-full w-2/3 rounded-full bg-[#16a085] shadow-[0_0_10px_rgba(22,160,133,0.5)] animate-[loading_2s_ease-in-out_infinite]"></div>
                            </div>
                        </div>

                        <div x-show="error" x-cloak class="rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-600 font-bold flex items-center gap-3" x-text="error"></div>
                        
                        <div class="grid gap-8">
                            <div>
                                <x-input-label for="csv_upload_batch_id" :value="__('Select Data Batch')" class="text-sm font-bold text-gray-700 mb-2 px-1" />
                                <div class="relative">
                                    <select id="csv_upload_batch_id" name="csv_upload_batch_id" x-model="selectedBatchId" @change="resetSelection()" required class="mt-1 block w-full rounded-2xl border-gray-100 focus:border-[#1abc9c] focus:ring-[#1abc9c]/20 shadow-sm transition-all text-sm font-bold h-14 pr-12 appearance-none bg-gray-50/50 focus:bg-white">
                                        @foreach ($batches as $b)
                                            <option value="{{ $b->id }}">{{ $b->filename }} ({{ $b->report_urls_count }} {{ __('rows') }})</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-5 pointer-events-none text-gray-400">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('csv_upload_batch_id')" class="mt-2 text-xs font-bold" />
                            </div>

                            {{-- Selection Mode Toggle --}}
                            <div class="space-y-3">
                                <x-input-label :value="__('Processing Scope')" class="text-sm font-bold text-gray-700 px-1" />
                                <div class="grid grid-cols-2 gap-4">
                                    <button type="button" @click="mode = 'all'" 
                                        :class="mode === 'all' ? 'bg-[#1abc9c]/10 border-[#1abc9c] text-[#16a085]' : 'bg-white border-gray-100 text-gray-500 hover:border-gray-200'"
                                        class="flex items-center justify-center gap-3 p-4 rounded-2xl border-2 transition-all group relative overflow-hidden">
                                        <div :class="mode === 'all' ? 'opacity-100' : 'opacity-0'" class="absolute inset-0 bg-gradient-to-br from-[#1abc9c]/5 to-transparent transition-opacity"></div>
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                        <span class="font-bold whitespace-nowrap">{{ __('All Rows') }}</span>
                                    </button>
                                    <button type="button" @click="mode = 'selective'" 
                                        :class="mode === 'selective' ? 'bg-[#1abc9c]/10 border-[#1abc9c] text-[#16a085]' : 'bg-white border-gray-100 text-gray-500 hover:border-gray-200'"
                                        class="flex items-center justify-center gap-3 p-4 rounded-2xl border-2 transition-all group relative overflow-hidden">
                                        <div :class="mode === 'selective' ? 'opacity-100' : 'opacity-0'" class="absolute inset-0 bg-gradient-to-br from-[#1abc9c]/5 to-transparent transition-opacity"></div>
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                        <span class="font-bold whitespace-nowrap">{{ __('Select Records') }}</span>
                                    </button>
                                </div>

                                {{-- Selected Info / Action --}}
                                <div x-show="mode === 'selective'" x-cloak x-transition.opacity class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                    <div class="flex items-center gap-3 text-sm font-bold text-gray-600">
                                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-[#16a085] text-white text-[10px]" x-text="selectedIds.length"></span>
                                        {{ __('records selected') }}
                                    </div>
                                    <button type="button" @click="openModal()" class="text-sm font-bold text-[#16a085] hover:text-[#1abc9c] flex items-center gap-2 transition-colors">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        {{ __('Modify Selection') }}
                                    </button>
                                </div>
                            </div>

                            <div>
                                <x-input-label for="prompt_id" :value="__('Select Prompt Configuration')" class="text-sm font-bold text-gray-700 mb-2 px-1" />
                                <div class="relative">
                                    <select id="prompt_id" name="prompt_id" required class="mt-1 block w-full rounded-2xl border-gray-100 focus:border-[#1abc9c] focus:ring-[#1abc9c]/20 shadow-sm transition-all text-sm font-bold h-14 pr-12 appearance-none bg-gray-50/50 focus:bg-white">
                                        @foreach ($prompts as $p)
                                            <option value="{{ $p->id }}">{{ $p->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-5 pointer-events-none text-gray-400">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('prompt_id')" class="mt-2 text-xs font-bold" />
                            </div>

                            <div class="flex items-start p-6 bg-[#1abc9c]/5 rounded-3xl border border-[#1abc9c]/10 hover:bg-[#1abc9c]/10 transition-colors cursor-pointer group" onclick="document.getElementById('dispatch').click()">
                                <input type="hidden" name="dispatch" value="0" />
                                <div class="flex items-center h-6">
                                    <input id="dispatch" name="dispatch" type="checkbox" value="1" 
                                        class="h-6 w-6 rounded border-gray-300 text-[#16a085] focus:ring-[#1abc9c] transition-all cursor-pointer shadow-sm" 
                                        checked />
                                </div>
                                <div class="ml-4">
                                    <label for="dispatch" class="text-base font-bold text-[#16a085] cursor-pointer block leading-tight">{{ __('Auto-dispatch jobs') }}</label>
                                    <p class="text-[10px] text-[#16a085]/60 uppercase tracking-widest font-bold mt-1 mb-2">{{ __('Recommended') }}</p>
                                    <p class="text-sm text-[#16a085]/70">{{ __('Immediately queue background workers to process these runs.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 pt-10 border-t border-gray-100">
                            <button type="submit" x-bind:disabled="submitting" 
                                class="flex-1 inline-flex items-center justify-center px-8 py-5 rounded-3xl bg-[#16a085] text-white font-bold text-lg shadow-[0_10px_30px_-10px_rgba(22,160,133,0.4)] hover:bg-[#1abc9c] hover:-translate-y-1 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-[#16a085]/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none active:scale-[0.98]">
                                <span x-show="!submitting" class="flex items-center">
                                    <svg class="mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    {{ __('Initialize Runs') }}
                                </span>
                                <span x-show="submitting" x-cloak class="flex items-center gap-3">
                                    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    {{ __('Processing…') }}
                                </span>
                            </button>
                            <a href="{{ route('qa-runs.index') }}" class="inline-flex items-center justify-center px-8 py-5 rounded-3xl bg-white border border-gray-200 text-gray-500 font-bold text-lg hover:bg-gray-50 transition-all duration-300">
                                {{ __('Cancel') }}
                            </a>
                        </div>

                        {{-- Selection Modal --}}
                        <template x-teleport="body">
                            <div x-show="modalOpen"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                                x-cloak>

                                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="modalOpen = false"></div>

                                <div class="relative bg-white w-full max-w-4xl max-h-[90vh] rounded-[2rem] shadow-2xl flex flex-col overflow-hidden border border-white/20">
                                    {{-- Modal Header --}}
                                    <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                                        <div>
                                            <h3 class="text-xl font-extrabold text-gray-900">{{ __('Select Records') }}</h3>
                                            <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mt-1">{{ __('Choose specific URL pairs to analyze') }}</p>
                                        </div>
                                        <button type="button" @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors bg-white p-2 rounded-xl shadow-sm border border-gray-100">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>

                                    {{-- Modal Search --}}
                                    <div class="px-8 py-4 border-b border-gray-100 bg-white">
                                        <div class="relative">
                                            <input type="text" x-model="modalSearch" @input.debounce.300ms="fetchModalData(1)"
                                                placeholder="{{ __('Search by URL…') }}"
                                                class="w-full pl-12 pr-4 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:border-[#1abc9c] focus:ring-[#1abc9c]/20 transition-all font-bold text-sm">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Modal Content --}}
                                    <div class="flex-1 overflow-y-auto p-8 relative min-h-[400px]">
                                        {{-- Loader --}}
                                        <div x-show="loadingModal" x-transition.opacity class="absolute inset-0 z-20 bg-white/60 backdrop-blur-xs flex items-center justify-center">
                                            <div class="flex flex-col items-center gap-4">
                                                <div class="h-12 w-12 rounded-2xl bg-[#1abc9c]/10 flex items-center justify-center text-[#16a085]">
                                                    <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                </div>
                                                <span class="text-sm font-bold text-[#16a085]">{{ __('Fetching data…') }}</span>
                                            </div>
                                        </div>

                                        <div x-show="modalData && modalData.data.length > 0">
                                            <table class="w-full text-sm text-left">
                                                <thead>
                                                    <tr class="text-xs font-bold text-[#16a085] uppercase tracking-wider">
                                                        <th class="py-3 px-4 w-10">
                                                            <input type="checkbox" @change="toggleAllOnPage()"
                                                                :checked="modalData && modalData.data.every(i => selectedIds.includes(i.id))"
                                                                class="rounded border-gray-300 text-[#16a085] focus:ring-[#16a085]/20 h-5 w-5 cursor-pointer transition-all">
                                                        </th>
                                                        <th class="py-3 px-4">{{ __('English URL') }}</th>
                                                        <th class="py-3 px-4">{{ __('Welsh URL') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-50">
                                                    <template x-for="item in modalData.data" :key="item.id">
                                                        <tr class="hover:bg-gray-50/80 transition-colors group cursor-pointer" @click="toggleSelection(item.id)">
                                                            <td class="py-4 px-4 uppercase font-bold text-gray-400">
                                                                <input type="checkbox" :checked="selectedIds.includes(item.id)"
                                                                    class="rounded border-gray-300 text-[#16a085] focus:ring-[#16a085]/20 h-5 w-5 cursor-pointer transition-all">
                                                            </td>
                                                            <td class="py-4 px-4">
                                                                <div class="text-gray-900 font-bold truncate max-w-[250px]" x-text="item.english_url"></div>
                                                            </td>
                                                            <td class="py-4 px-4 font-medium text-gray-500 truncate max-w-[250px]" x-text="item.welsh_url"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>

                                            {{-- Modal Pagination --}}
                                            <div class="mt-8 flex items-center justify-between border-t border-gray-100 pt-6">
                                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest"
                                                    x-text="`{{ __('Showing') }} ${modalData.from} - ${modalData.to} {{ __('of') }} ${modalData.total}`"></span>
                                                <div class="flex gap-2">
                                                    <button type="button" @click="fetchModalData(modalPage - 1)" :disabled="modalPage === 1"
                                                        class="p-2 rounded-xl border border-gray-100 bg-white text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all shadow-sm">
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                                    </button>
                                                    <button type="button" @click="fetchModalData(modalPage + 1)" :disabled="modalPage === modalData.last_page"
                                                        class="p-2 rounded-xl border border-gray-100 bg-white text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all shadow-sm">
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div x-show="modalData && modalData.data.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-500">
                                            <svg class="h-16 w-16 text-gray-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <p class="font-bold uppercase tracking-widest text-xs">{{ __('No records found matching your search.') }}</p>
                                        </div>
                                    </div>

                                    {{-- Modal Footer --}}
                                    <div class="px-8 py-6 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                                        <div class="text-sm font-bold text-gray-600">
                                            <span x-text="selectedIds.length" class="text-[#16a085]"></span> {{ __('records selected globally') }}
                                        </div>
                                        <div class="flex gap-4">
                                            <button type="button" @click="selectedIds = [];" class="px-5 py-2 rounded-xl text-xs font-bold text-gray-500 hover:text-red-600 transition-colors uppercase tracking-widest">
                                                {{ __('Reset All') }}
                                            </button>
                                            <button type="button" @click="modalOpen = false" class="px-8 py-3 rounded-2xl bg-[#16a085] text-white font-bold text-sm shadow-lg hover:bg-[#1abc9c] transition-all duration-300 active:scale-[0.98]">
                                                {{ __('Apply Selection') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
