<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AiModelController extends Controller
{
    public function index(): View
    {
        $models = AiModel::query()->orderByDesc('is_default')->orderBy('name')->paginate(15);

        return view('ai_models.index', compact('models'));
    }

    public function create(): View
    {
        $aiModel = new AiModel(['is_default' => false]);

        return view('ai_models.create', compact('aiModel'));
    }

    public function show(AiModel $model): View
    {
        return view('ai_models.show', ['aiModel' => $model]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data): void {
            if ($data['is_default']) {
                AiModel::query()->update(['is_default' => false]);
            }
            AiModel::query()->create($data);
        });

        return redirect()->route('models.index')->with('status', __('Model created.'));
    }

    public function edit(AiModel $model): View
    {
        return view('ai_models.edit', ['aiModel' => $model]);
    }

    public function update(Request $request, AiModel $model): RedirectResponse
    {
        $data = $this->validated($request, $model);

        DB::transaction(function () use ($data, $model): void {
            if ($data['is_default']) {
                AiModel::query()->whereKeyNot($model->id)->update(['is_default' => false]);
            }
            $model->update($data);
        });

        return redirect()->route('models.index')->with('status', __('Model updated.'));
    }

    public function destroy(AiModel $model): RedirectResponse
    {
        if ($model->qaRuns()->exists()) {
            return redirect()->route('models.index')
                ->withErrors(['delete' => __('This model cannot be deleted because it is used by one or more QA runs.')]);
        }

        $model->delete();

        return redirect()->route('models.index')->with('status', __('Model deleted.'));
    }

    /**
     * @return array{name: string, note: string|null, is_default: bool}
     */
    private function validated(Request $request, ?AiModel $model = null): array
    {
        $request->merge([
            'name' => Str::lower(trim((string) $request->input('name', ''))),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9][a-z0-9._-]*$/',
                Rule::unique('ai_models', 'name')->ignore($model?->id),
            ],
            'note' => ['nullable', 'string'],
            'is_default' => ['boolean'],
        ]);

        return [
            'name' => $validated['name'],
            'note' => isset($validated['note']) && trim((string) $validated['note']) !== ''
                ? $validated['note']
                : null,
            'is_default' => $request->boolean('is_default'),
        ];
    }
}
