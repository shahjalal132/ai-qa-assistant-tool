<div class="space-y-4">
    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $prompt->title ?? '')" required />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="system_instruction" :value="__('System instruction')" />
        <textarea id="system_instruction" name="system_instruction" rows="12" required
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-mono">{{ old('system_instruction', $prompt->system_instruction ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('system_instruction')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="response_schema" :value="__('Response schema (JSON, optional — Gemini structured output)')" />
        <textarea id="response_schema" name="response_schema" rows="8"
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-mono"
            placeholder="{}">{{ old('response_schema', isset($prompt) && $prompt->response_schema ? json_encode($prompt->response_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
        <x-input-error :messages="$errors->get('response_schema')" class="mt-2" />
    </div>
    <div class="flex items-center gap-2">
        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm"
            @checked(old('is_active', $prompt->is_active ?? true)) />
        <x-input-label for="is_active" :value="__('Active')" />
    </div>
</div>
