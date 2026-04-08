<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromptController extends Controller
{
    public function index(): View
    {
        $prompts = Prompt::query()->latest()->paginate(15);

        return view('prompts.index', compact('prompts'));
    }

    public function create(): View
    {
        $prompt = new Prompt(['is_active' => true]);

        return view('prompts.create', compact('prompt'));
    }

    public function show(Prompt $prompt): View
    {
        return view('prompts.show', compact('prompt'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Prompt::query()->create($data);

        return redirect()->route('prompts.index')->with('status', __('Prompt created.'));
    }

    public function edit(Prompt $prompt): View
    {
        return view('prompts.edit', compact('prompt'));
    }

    public function update(Request $request, Prompt $prompt): RedirectResponse
    {
        $data = $this->validated($request);
        $prompt->update($data);

        return redirect()->route('prompts.index')->with('status', __('Prompt updated.'));
    }

    public function destroy(Prompt $prompt): RedirectResponse
    {
        $prompt->delete();

        return redirect()->route('prompts.index')->with('status', __('Prompt deleted.'));
    }

    /**
     * @return array{title: string, system_instruction: string, response_schema: array<string, mixed>|null, is_active: bool}
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'system_instruction' => ['required', 'string'],
            'response_schema' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $schemaRaw = $validated['response_schema'] ?? null;
        $schema = null;
        if (is_string($schemaRaw) && trim($schemaRaw) !== '') {
            $decoded = json_decode($schemaRaw, true);
            if (! is_array($decoded)) {
                throw ValidationException::withMessages([
                    'response_schema' => __('response_schema must be valid JSON.'),
                ]);
            }
            $schema = $decoded;
        }

        return [
            'title' => $validated['title'],
            'system_instruction' => $validated['system_instruction'],
            'response_schema' => $schema,
            'is_active' => (bool) ($request->boolean('is_active')),
        ];
    }
}
