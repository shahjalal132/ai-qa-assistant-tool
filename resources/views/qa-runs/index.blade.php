<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('QA runs') }}</h2>
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
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ __('QA Execution Runs') }}</h1>
                    <p class="text-sm text-gray-500 mt-1 font-medium">{{ __('Monitor and manage your AI-powered quality assurance processes.') }}</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                    <form method="get" class="flex items-center gap-3 bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm flex-1 lg:flex-none">
                        <label for="status" class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Filter') }}</label>
                        <select name="status" id="status" class="border-none bg-transparent text-sm font-bold text-[#16a085] focus:ring-0 p-0 pr-8" onchange="this.form.submit()">
                            <option value="">{{ __('All Statuses') }}</option>
                            @foreach (['pending', 'processing', 'completed', 'failed'] as $s)
                                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </form>
                    
                    <a href="{{ route('qa-runs.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#16a085] px-6 py-3 text-sm font-bold text-white shadow-lg hover:bg-[#1abc9c] hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-[#16a085]/20 whitespace-nowrap">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('New Run') }}
                    </a>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md border border-gray-100 shadow-xl sm:rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider w-16">ID</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Configuration') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Target URL') }}</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-[#16a085] uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm">
                            @forelse ($runs as $run)
                                <tr class="hover:bg-gray-50/80 transition-colors group">
                                    <td class="px-6 py-4 text-gray-400 font-bold">#{{ $run->id }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-lg bg-[#1abc9c]/10 flex items-center justify-center text-[#16a085] mr-3">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                            </div>
                                            <span class="font-bold text-gray-900">{{ $run->prompt->title ?? 'Default Analysis' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-xs truncate text-gray-500 font-medium" title="{{ $run->reportUrl->english_url ?? '' }}">
                                            {{ $run->reportUrl->english_url ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-gray-100 text-gray-500',
                                                'processing' => 'bg-blue-50 text-blue-600 animate-pulse',
                                                'completed' => 'bg-[#1abc9c]/10 text-[#16a085]',
                                                'failed' => 'bg-red-50 text-red-600',
                                            ];
                                            $colorClass = $statusColors[$run->status] ?? 'bg-gray-100 text-gray-500';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $colorClass }}">
                                            {{ $run->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('qa-runs.show', $run) }}" class="text-[#16a085] hover:text-[#1abc9c] font-bold transition-colors">{{ __('View') }}</a>
                                            
                                            <form action="{{ route('qa-runs.toggle', $run) }}" method="post" class="inline">
                                                @csrf
                                                <button type="submit" class="text-gray-400 hover:text-gray-600 transition-colors" title="{{ $run->is_active ? __('Deactivate') : __('Activate') }}">
                                                    @if($run->is_active)
                                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                                                        </svg>
                                                    @else
                                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                                        </svg>
                                                    @endif
                                                </button>
                                            </form>

                                            @if ($run->status === 'failed' || $run->status === 'completed')
                                                <form action="{{ route('qa-runs.retry', $run) }}" method="post" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-amber-500 hover:text-amber-700 transition-colors" title="{{ __('Retry Analysis') }}">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('qa-runs.destroy', $run) }}" method="post" class="inline" onsubmit="return confirm(@json(__('Delete this run?')));">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-red-400 hover:text-red-600 transition-colors">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-gray-200 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="font-bold tracking-tight">{{ __('No execution runs found.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="px-2">{{ $runs->links() }}</div>
        </div>
    </div>
</x-app-layout>
