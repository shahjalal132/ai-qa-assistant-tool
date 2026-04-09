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

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2">
                <div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('csv-upload-batches.index') }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-[#16a085] hover:border-[#1abc9c]/30 transition-all shadow-sm">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ __('Batch Details') }}</h1>
                            <p class="text-sm text-gray-500 font-medium">{{ $batch->filename }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mr-3">{{ __('Status') }}</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-[#1abc9c]/10 text-[#16a085]">
                        <span class="h-2 w-2 rounded-full bg-[#1abc9c] animate-pulse mr-2"></span>
                        {{ __(':count URL pair(s)', ['count' => $batch->reportUrls->count()]) }}
                    </span>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-xl sm:rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider w-16">#</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('English URL') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Welsh URL') }}</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('System Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($batch->reportUrls as $row)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-bold text-gray-400">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-md break-all text-sm text-gray-600 font-medium leading-relaxed">
                                            {{ $row->english_url }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-md break-all text-sm text-gray-600 font-medium leading-relaxed">
                                            {{ $row->welsh_url }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                            {{ $row->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="flex justify-center pt-4">
                 <a href="{{ route('csv-upload-batches.index') }}" class="text-xs font-bold text-gray-400 hover:text-[#16a085] uppercase tracking-widest transition-colors flex items-center gap-2">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Back to all batches') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
