<?php

namespace App\Http\Controllers;

use App\Enums\InputCategory;
use App\Models\DailyReport;
use App\Models\Input;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TreatmentController extends Controller
{
    public function create(DailyReport $report): View
    {
        $medicines = Input::ofCategory(InputCategory::Medicine)
            ->withStock()
            ->orderBy('name')
            ->get();

        return view('treatments.create', compact('report', 'medicines'));
    }

    public function store(Request $request, DailyReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'input_id' => [
                'nullable',
                Rule::exists('inputs', 'id')->where('category', InputCategory::Medicine->value),
            ],
            'name' => ['required_without:input_id', 'nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:255'],
            'quantity_used' => ['required_with:input_id', 'nullable', 'numeric', 'gt:0'],
            'administration_method' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'birds_count' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        if (blank($validated['name'] ?? null)) {
            $validated['name'] = Input::findOrFail($validated['input_id'])->name;
        }

        $report->treatments()->create($validated);

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Traitement enregistré.');
    }
}
