<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;
use App\Models\Treatment;

class TreatmentController extends Controller
{
    public function create(DailyReport $report)
    {
        return view('treatments.create', compact('report'));
    }

    public function store(Request $request, DailyReport $report)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:255'],
            'administration_method' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'birds_count' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $report->treatments()->create($validated);

        return redirect()
            ->route('reports.index')
            ->with('success', 'Traitement enregistré.');
    }
}
