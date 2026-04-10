@php
    $jsonPretty = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="title" :value="__('Prompt Title')" class="text-sm font-bold text-gray-700 mb-1" />
        <x-text-input id="title" name="title" type="text" 
            class="mt-1 block w-full rounded-xl border-gray-200 focus:border-[#1abc9c] focus:ring-[#1abc9c]/20 shadow-sm transition-all text-sm font-medium h-11" 
            :value="old('title', $prompt->title ?? '')" 
            placeholder="{{ __('e.g. Content QA Analysis Prompt') }}"
            required />
        <x-input-error :messages="$errors->get('title')" class="mt-2 text-xs font-bold" />
    </div>

    <div>
        <x-input-label for="system_instruction" :value="__('System Instruction')" class="text-sm font-bold text-gray-700 mb-1" />
        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">{{ __('Plain text — newlines preserved; examples like :tag remain visible to the model.', ['tag' => '<h1>']) }}</p>
        <div class="relative group mt-1 rounded-xl border border-gray-200 bg-gray-50/30 shadow-sm transition-all focus-within:border-[#1abc9c] focus-within:ring-2 focus-within:ring-[#1abc9c]/20 focus-within:bg-white overflow-hidden">
            <textarea id="system_instruction" name="system_instruction" rows="22" required
                spellcheck="false" autocomplete="off" autocorrect="off" autocapitalize="off"
                class="block w-full min-h-[20rem] max-h-[min(70vh,48rem)] resize-y border-0 bg-transparent px-4 py-3 text-sm font-mono leading-relaxed text-gray-900 whitespace-pre-wrap [tab-size:4] focus:ring-0 focus:outline-none"
                placeholder="{{ __('Enter the core instructions for the AI…') }}">{{ old('system_instruction', $prompt->system_instruction ?? '') }}</textarea>
            <div class="absolute bottom-3 right-3 text-[10px] font-bold text-gray-300 pointer-events-none uppercase tracking-widest z-10">{{ __('Instruction set') }}</div>
        </div>
        <x-input-error :messages="$errors->get('system_instruction')" class="mt-2 text-xs font-bold" />
    </div>

    <div>
        <x-input-label for="response_schema" :value="__('Response Schema (JSON)')" class="text-sm font-bold text-gray-700 mb-1" />
        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">{{ __('Optional — Enables Gemini Structured Output') }}</p>
        <div class="relative group mt-1 rounded-xl border border-gray-200 bg-gray-50/30 shadow-sm transition-all focus-within:border-[#1abc9c] focus-within:ring-2 focus-within:ring-[#1abc9c]/20 focus-within:bg-white overflow-hidden">
            <textarea id="response_schema" name="response_schema" rows="22"
                spellcheck="false" autocomplete="off" autocorrect="off" autocapitalize="off"
                class="block w-full min-h-[16rem] max-h-[min(60vh,40rem)] resize-y border-0 bg-transparent px-4 py-3 text-sm font-mono leading-relaxed text-gray-900 whitespace-pre [tab-size:4] focus:ring-0 focus:outline-none"
                placeholder="{}">{{ old('response_schema', isset($prompt) && $prompt->response_schema ? json_encode($prompt->response_schema, $jsonPretty) : '') }}</textarea>
            <div class="absolute bottom-3 right-3 text-[10px] font-bold text-gray-300 pointer-events-none uppercase tracking-widest z-10">{{ __('JSON Schema') }}</div>
        </div>
        <x-input-error :messages="$errors->get('response_schema')" class="mt-2 text-xs font-bold" />
    </div>

    <div class="flex items-center p-4 bg-[#1abc9c]/5 rounded-xl border border-[#1abc9c]/10 hover:bg-[#1abc9c]/10 transition-colors cursor-pointer group" onclick="document.getElementById('is_active').click()">
        <div class="flex items-center h-5">
            <input id="is_active" name="is_active" type="checkbox" value="1" 
                class="h-5 w-5 rounded border-gray-300 text-[#16a085] focus:ring-[#1abc9c] transition-all cursor-pointer"
                @checked(old('is_active', $prompt->is_active ?? true)) />
        </div>
        <div class="ml-3">
            <label for="is_active" class="text-sm font-bold text-[#16a085] cursor-pointer">{{ __('Active Status') }}</label>
            <p class="text-xs text-[#16a085]/70">{{ __('When inactive, this prompt will be hidden from the analysis selector.') }}</p>
        </div>
    </div>
</div>
