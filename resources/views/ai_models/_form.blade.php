<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Model name')" class="text-sm font-bold text-gray-700 mb-1" />
        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">{{ __('Lowercase Gemini model id') }}</p>
        <x-text-input id="name" name="name" type="text"
            class="mt-1 block w-full rounded-xl border-gray-200 focus:border-[#1abc9c] focus:ring-[#1abc9c]/20 shadow-sm transition-all text-sm font-medium h-11"
            :value="old('name', $aiModel->name ?? '')"
            placeholder="{{ __('e.g. gemini-2.0-flash') }}"
            required
            autocomplete="off" />
        <x-input-error :messages="$errors->get('name')" class="mt-2 text-xs font-bold" />
    </div>

    <div>
        <x-input-label for="note" :value="__('Note')" class="text-sm font-bold text-gray-700 mb-1" />
        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">{{ __('Optional') }}</p>
        <textarea id="note" name="note" rows="4"
            class="mt-1 block w-full border-gray-200 focus:border-[#1abc9c] focus:ring-[#1abc9c]/20 rounded-xl shadow-sm text-sm font-medium bg-gray-50/30 transition-all focus:bg-white leading-relaxed"
            placeholder="{{ __('Internal description or usage hints…') }}">{{ old('note', $aiModel->note ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('note')" class="mt-2 text-xs font-bold" />
    </div>

    <div class="flex items-center p-4 bg-[#1abc9c]/5 rounded-xl border border-[#1abc9c]/10 hover:bg-[#1abc9c]/10 transition-colors cursor-pointer group" onclick="document.getElementById('is_default').click()">
        <div class="flex items-center h-5">
            <input id="is_default" name="is_default" type="checkbox" value="1"
                class="h-5 w-5 rounded border-gray-300 text-[#16a085] focus:ring-[#1abc9c] transition-all cursor-pointer"
                @checked(old('is_default', $aiModel->is_default ?? false)) />
        </div>
        <div class="ml-3">
            <label for="is_default" class="text-sm font-bold text-[#16a085] cursor-pointer">{{ __('Use as default') }}</label>
            <p class="text-xs text-[#16a085]/70">{{ __('Pre-selected when creating QA runs. Only one model can be default.') }}</p>
        </div>
    </div>
</div>
