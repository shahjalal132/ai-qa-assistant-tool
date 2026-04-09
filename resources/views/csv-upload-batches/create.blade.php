<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload CSV') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-2xl sm:rounded-3xl p-8 sm:p-12">
                <div class="text-center mb-10">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-[#1abc9c]/10 text-[#16a085] mb-4">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Upload CSV Data') }}</h1>
                    <p class="text-gray-500 mt-3 text-lg">{{ __('Import your QA data batches efficiently.') }}</p>
                </div>

                <div class="bg-[#16a085]/5 border-s-4 border-[#16a085] p-4 mb-8 rounded-e-xl">
                    <p class="text-sm text-[#16a085] leading-relaxed">
                        <span class="font-bold uppercase tracking-wider text-[10px] block mb-1">{{ __('Required Columns') }}</span>
                        {{ __('Ensure your CSV contains') }} <code class="bg-white px-1.5 py-0.5 rounded border border-[#16a085]/20 font-bold">english_url</code> {{ __('and') }} <code class="bg-white px-1.5 py-0.5 rounded border border-[#16a085]/20 font-bold">welsh_url</code>. {{ __('Other columns will be saved as metadata.') }}
                    </p>
                </div>

                <form action="{{ route('csv-upload-batches.store') }}" method="post" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    <div class="relative group">
                        <label for="csv" class="block text-sm font-bold text-gray-700 mb-2">{{ __('Select CSV File') }}</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-2xl group-hover:border-[#1abc9c] transition-colors bg-gray-50/30">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-[#1abc9c] transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="csv" class="relative cursor-pointer bg-white rounded-md font-bold text-[#16a085] hover:text-[#1abc9c] focus-within:outline-none transition-colors">
                                        <span>{{ __('Click to upload') }}</span>
                                        <input id="csv" name="csv" type="file" accept=".csv,.txt,text/csv" required class="sr-only">
                                    </label>
                                    <p class="pl-1">{{ __('or drag and drop') }}</p>
                                </div>
                                <p class="text-xs text-gray-500">{{ __('CSV or TXT files only') }}</p>
                                <p id="file-name-display" class="text-sm font-semibold text-[#16a085] mt-2 hidden italic"></p>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('csv')" class="mt-2 text-sm font-medium" />
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <button type="submit" class="flex-1 inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-[#16a085] text-white font-bold text-lg shadow-lg hover:bg-[#1abc9c] hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-[#16a085]/20">
                            {{ __('Start Import Process') }}
                        </button>
                        <a href="{{ route('csv-upload-batches.index') }}" class="inline-flex items-center justify-center px-8 py-4 rounded-2xl bg-white border-2 border-gray-100 text-gray-600 font-bold text-lg hover:bg-gray-50 transition-all duration-200">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('csv').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const display = document.getElementById('file-name-display');
            if (fileName) {
                display.textContent = 'Selected: ' + fileName;
                display.classList.remove('hidden');
            } else {
                display.classList.add('hidden');
            }
        });
    </script>
</x-app-layout>
