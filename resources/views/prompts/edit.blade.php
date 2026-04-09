<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit prompt') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-2xl sm:rounded-3xl p-8 sm:p-12">
                <div class="mb-10">
                    <div class="inline-flex items-center gap-3 mb-4">
                        <div class="h-12 w-12 rounded-2xl bg-[#1abc9c]/10 text-[#16a085] flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Update Prompt') }}</h1>
                    </div>
                    <p class="text-gray-500 text-lg font-medium">{{ __('Refine your system instructions or response schemas for analysis.') }}</p>
                </div>

                <form method="post" action="{{ route('prompts.update', $prompt) }}" class="space-y-10">
                    @csrf
                    @method('patch')
                    @include('prompts._form', ['prompt' => $prompt])
                    
                    <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-50">
                        <button type="submit" class="flex-1 inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-[#16a085] text-white font-bold text-lg shadow-lg hover:bg-[#1abc9c] hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-[#16a085]/20">
                            {{ __('Update & Synchronize') }}
                        </button>
                        <a href="{{ route('prompts.index') }}" class="inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-white border-2 border-gray-100 text-gray-600 font-bold text-lg hover:bg-gray-50 transition-all duration-200">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
