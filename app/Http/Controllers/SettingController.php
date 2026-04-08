<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $geminiKey = Setting::getValue('gemini_api_key', '');
        $useDummy = filter_var(Setting::getValue('qa_use_dummy_ai', ''), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($useDummy === null) {
            $useDummy = (bool) config('qa.use_dummy_ai', true);
        }

        return view('settings.index', [
            'gemini_api_key' => $geminiKey,
            'use_dummy_ai' => $useDummy,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'gemini_api_key' => ['nullable', 'string', 'max:500'],
            'use_dummy_ai' => ['sometimes', 'boolean'],
        ]);

        Setting::setValue('gemini_api_key', $data['gemini_api_key'] ?? '');
        Setting::setValue('qa_use_dummy_ai', $request->boolean('use_dummy_ai') ? '1' : '0');

        return redirect()->route('settings.index')->with('status', __('Settings saved.'));
    }
}
