<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyReport;


class DailyReportController extends Controller
{
    public function index()
    {
        $reports = DailyReport::with([
            'treatments',
            'vaccinations'
        ])
        ->orderBy('report_date', 'desc')
        ->get();
        return view('reports.index', compact('reports'));

        
    }

    public function create()
    {
        return view('reports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_date' => ['required', 'date', 'unique:daily_reports,report_date'],
            'birds_count' => ['required', 'integer', 'min:0'],
            'deaths_count' => ['nullable', 'integer', 'min:0'],
            'sick_count' => ['nullable', 'integer', 'min:0'],
            'feed_quantity' => ['nullable', 'numeric', 'min:0'],
            'water_quantity' => ['nullable', 'numeric', 'min:0'],
            'birds_weight' => ['nullable', 'numeric', 'min:0'],
            'temperature' => ['nullable', 'numeric'],
            'observations' => ['nullable', 'string'],
        ]);

        DailyReport::create($validated);

        return redirect()
            ->route('reports.index')
            ->with('success', 'Rapport enregistré avec succès.');
    }
}
