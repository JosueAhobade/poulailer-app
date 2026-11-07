<?php

namespace App\Http\Controllers;

use App\Enums\InputCategory;
use App\Models\DailyReport;
use App\Models\Input;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VaccinationController extends Controller
{
    public function create(DailyReport $report): View
    {
        $vaccines = Input::ofCategory(InputCategory::Vaccine)
            ->withStock()
            ->orderBy('name')
            ->get();

        return view('vaccinations.create', compact('report', 'vaccines'));
    }

    public function store(Request $request, DailyReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'input_id' => [
                'nullable',
                Rule::exists('inputs', 'id')->where('category', InputCategory::Vaccine->value),
            ],
            'vaccine_name' => ['required_without:input_id', 'nullable', 'string', 'max:255'],
            'disease' => ['nullable', 'string', 'max:255'],
            'administration_method' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:255'],
            'quantity_used' => ['required_with:input_id', 'nullable', 'numeric', 'gt:0'],
            'rappel' => ['nullable', 'boolean'],
            'birds_count' => [
                'required',
                'integer',
                'min:1',
                'max:'.$report->birds_count,
            ],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        if (blank($validated['vaccine_name'] ?? null)) {
            $validated['vaccine_name'] = Input::findOrFail($validated['input_id'])->name;
        }

        $report->vaccinations()->create($validated);

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Vaccination enregistrée avec succès.');
    }
}
