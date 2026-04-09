<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Settings') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ __('System Settings') }}</h1>
                <p class="text-sm text-gray-500 mt-2 font-medium">{{ __('Configure your AI credentials and brand identity.') }}</p>
            </div>

            @if (session('status'))
                <div class="rounded-xl bg-[#1abc9c]/10 border border-[#1abc9c]/20 p-4 text-sm text-[#16a085] flex items-center gap-3 shadow-sm mb-6">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-2xl sm:rounded-3xl overflow-hidden">
                <form method="post" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="p-8 sm:p-10 space-y-8">
                    @csrf
                    
                    <div class="space-y-6">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Brand Identity') }}</h3>
                        
                        <div class="flex flex-col sm:flex-row items-center gap-8 p-6 bg-gray-50/50 rounded-2xl border border-gray-100">
                            <div class="flex-none">
                                <div class="relative h-24 w-24 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-center justify-center overflow-hidden group">
                                    @if($app_logo)
                                        <img src="{{ asset('storage/' . $app_logo) }}" alt="App Logo" class="max-h-16 max-w-16 rounded-full object-contain">
                                    @else
                                        <svg class="h-10 w-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <input type="file" name="app_logo" id="app_logo" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                                </div>
                                <p class="text-[10px] text-center mt-2 font-bold text-[#16a085] uppercase tracking-wider">{{ __('Update Logo') }}</p>
                            </div>
                            <div class="flex-1 text-center sm:text-left">
                                <h4 class="text-sm font-bold text-gray-900">{{ __('Application Logo') }}</h4>
                                <p class="text-xs text-gray-500 mt-1">{{ __('Upload a PNG, JPG or SVG. Recommended size 512x512px.') }}</p>
                                <x-input-error :messages="$errors->get('app_logo')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 pt-4 border-t border-gray-50">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('AI Configuration') }}</h3>
                        
                        <div>
                            <x-input-label for="gemini_api_key" :value="__('Gemini API Key')" class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2" />
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#16a085] transition-colors">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                                <x-text-input id="gemini_api_key" name="gemini_api_key" type="password" class="block w-full pl-12 py-3 rounded-xl border-gray-100 focus:border-[#1abc9c] focus:ring-[#1abc9c]/20 transition-all font-mono text-sm" :value="old('gemini_api_key', $gemini_api_key)" placeholder="••••••••••••••••" />
                            </div>
                            <p class="mt-2 text-[11px] text-gray-400 font-medium italic">{{ __('Leave blank to keep current key. Key is AES-256 encrypted in storage.') }}</p>
                            <x-input-error :messages="$errors->get('gemini_api_key')" class="mt-2" />
                        </div>

                        <div class="flex items-center p-4 bg-gray-50/30 rounded-xl border border-gray-50 group hover:border-[#1abc9c]/20 transition-all">
                             <div class="flex items-center h-5">
                                <input id="use_dummy_ai" name="use_dummy_ai" type="checkbox" value="1" class="h-5 w-5 rounded-md border-gray-200 text-[#16a085] focus:ring-[#1abc9c]/40 transition-all cursor-pointer" @checked(old('use_dummy_ai', $use_dummy_ai))>
                            </div>
                            <div class="ml-4 text-sm">
                                <x-input-label for="use_dummy_ai" :value="__('Enable Dummy AI Mode')" class="font-bold text-gray-700 cursor-pointer" />
                                <p class="text-xs text-gray-500 font-medium">{{ __('Bypass actual Gemini API calls for testing purposes.') }}</p>
                            </div>
                            <input type="hidden" name="use_dummy_ai" value="0" />
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center px-10 py-4 rounded-xl bg-[#16a085] text-white font-bold text-sm shadow-xl hover:bg-[#1abc9c] hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">
                             <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            {{ __('Save Preferences') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
