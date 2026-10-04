<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;

class VaccinationController extends Controller
{
    public function create(DailyReport $report)
    {
        return view('vaccinations.create', compact('report'));
    }

    public function store(Request $request, DailyReport $report)
    {
        $validated = $request->validate([
            'vaccine_name' => ['required', 'string', 'max:255'],
            'disease' => ['nullable', 'string', 'max:255'],
            'administration_method' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:255'],
            'rappel' => ['nullable', 'boolean'],
            'birds_count' => [
                'required',
                'integer',
                'min:1',
                'max:' . $report->birds_count,
            ],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $report->vaccinations()->create($validated);

        return redirect()
            ->route('reports.index')
            ->with('success', 'Vaccination enregistrée avec succès.');
    }
}
