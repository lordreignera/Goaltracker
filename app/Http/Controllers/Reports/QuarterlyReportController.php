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
            ->setPaper('a4', 'landscape')
            ->download(str($quarter->name)->slug('-').'-quarterly-report.pdf');
    }

    public function csv(Request $request, QuarterlyReportService $reports)
    {
        $quarter = $this->selectedQuarter($request);
        $data = $reports->build($quarter, $request->user());
        $filename = str($quarter->name)->slug('-').'-daily-report-table.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Goal',
                'Sub-goal',
                'Timeline',
                'Reporting Frequency',
                'Reporting Period',
                'Report Date',
                'Progress Update',
                'Staff Claim %',
                'Supervisor Verified %',
                'Achievement',
                'Challenges',
                'Action Point',
                'Evidence',
                'Staff',
                'Status',
                'Supervisor Feedback',
            ]);

            foreach ($data['reportRows'] as $row) {
                fputcsv($handle, [
                    $row['goal'],
                    $row['objective'],
                    $row['timeline'],
                    ucfirst($row['reporting_frequency']),
                    $row['report_period'],
                    $row['report_date']?->format('Y-m-d'),
                    $row['is_progress_update'] ? 'Yes' : 'No',
                    $row['achievement_percentage'],
                    $row['verified_percentage'],
                    $row['achievement_summary'],
                    $row['challenges'],
                    $row['action_points'],
                    $row['evidence_name'] ?: 'No evidence',
                    $row['staff'],
                    str_replace('_', ' ', $row['status']),
                    $row['review_comments'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
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
