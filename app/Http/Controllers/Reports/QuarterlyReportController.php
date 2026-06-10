<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Quarter;
use App\Services\QuarterlyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuarterlyReportController extends Controller
{
    public function index(Request $request, QuarterlyReportService $reports)
    {
        $quarter = $this->selectedQuarter($request);

        return view('reports.quarterly', $reports->build($quarter, $request->user()) + [
            'quarters' => Quarter::orderByDesc('starts_at')->get(),
            'selectedQuarter' => $quarter,
        ]);
    }

    public function pdf(Request $request, QuarterlyReportService $reports)
    {
        $quarter = $this->selectedQuarter($request);
        $data = $reports->build($quarter, $request->user()) + [
            'generatedBy' => $request->user(),
        ];

        return Pdf::loadView('reports.quarterly-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download(str($quarter->name)->slug('-').'-quarterly-report.pdf');
    }

    private function selectedQuarter(Request $request): Quarter
    {
        if ($request->filled('quarter_id')) {
            return Quarter::findOrFail($request->integer('quarter_id'));
        }

        return Quarter::where('is_active', true)->first()
            ?? Quarter::orderByDesc('starts_at')->firstOrFail();
    }
}
