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
                            async submitRuns(e) {
                                e.preventDefault();
                                this.submitting = true;
                                this.error = '';
                                const form = e.target;
                                const fd = new FormData(form);
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
                            <style>
                                @keyframes loading {
                                    0% { transform: translateX(-100%); }
                                    100% { transform: translateX(150%); }
                                }
                            </style>
                        </div>

                        <div x-show="error" x-cloak class="rounded-xl bg-red-50 border border-red-100 p-4 text-sm text-red-600 font-bold flex items-center gap-3" x-text="error"></div>
                        
                        <div class="grid gap-6">
                            <div>
                                <x-input-label for="csv_upload_batch_id" :value="__('Select Data Batch')" class="text-sm font-bold text-gray-700 mb-1" />
                                <div class="relative">
                                    <select id="csv_upload_batch_id" name="csv_upload_batch_id" required class="mt-1 block w-full rounded-xl border-gray-200 focus:border-[#1abc9c] focus:ring-[#1abc9c]/20 shadow-sm transition-all text-sm font-bold h-12 pr-10 appearance-none bg-gray-50/30 focus:bg-white">
                                        @foreach ($batches as $b)
                                            <option value="{{ $b->id }}">{{ $b->filename }} ({{ $b->report_urls_count }} {{ __('rows') }})</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('csv_upload_batch_id')" class="mt-2 text-xs font-bold" />
                            </div>

                            <div>
                                <x-input-label for="prompt_id" :value="__('Select Prompt Configuration')" class="text-sm font-bold text-gray-700 mb-1" />
                                <div class="relative">
                                    <select id="prompt_id" name="prompt_id" required class="mt-1 block w-full rounded-xl border-gray-200 focus:border-[#1abc9c] focus:ring-[#1abc9c]/20 shadow-sm transition-all text-sm font-bold h-12 pr-10 appearance-none bg-gray-50/30 focus:bg-white">
                                        @foreach ($prompts as $p)
                                            <option value="{{ $p->id }}">{{ $p->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('prompt_id')" class="mt-2 text-xs font-bold" />
                            </div>
                        </div>

                        <div class="flex items-start p-4 bg-[#1abc9c]/5 rounded-2xl border border-[#1abc9c]/10 hover:bg-[#1abc9c]/10 transition-colors cursor-pointer group" onclick="document.getElementById('dispatch').click()">
                            <input type="hidden" name="dispatch" value="0" />
                            <div class="flex items-center h-6">
                                <input id="dispatch" name="dispatch" type="checkbox" value="1" 
                                    class="h-5 w-5 rounded border-gray-300 text-[#16a085] focus:ring-[#1abc9c] transition-all cursor-pointer" 
                                    checked />
                            </div>
                            <div class="ml-3">
                                <label for="dispatch" class="text-sm font-bold text-[#16a085] cursor-pointer">{{ __('Auto-dispatch jobs') }}</label>
                                <p class="text-[10px] text-[#16a085]/70 uppercase tracking-widest font-bold">{{ __('Recommended') }}</p>
                                <p class="text-xs text-[#16a085]/70 mt-1">{{ __('Immediately queue background workers to process these runs.') }}</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-50">
                            <button type="submit" x-bind:disabled="submitting" 
                                class="flex-1 inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-[#16a085] text-white font-bold text-lg shadow-lg hover:bg-[#1abc9c] hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-[#16a085]/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                <span x-show="!submitting" class="flex items-center">
                                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    {{ __('Initialize Runs') }}
                                </span>
                                <span x-show="submitting" x-cloak>{{ __('Starting Batch…') }}</span>
                            </button>
                            <a href="{{ route('qa-runs.index') }}" class="inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-white border-2 border-gray-100 text-gray-600 font-bold text-lg hover:bg-gray-50 transition-all duration-200">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
