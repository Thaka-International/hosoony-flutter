<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportsController extends Controller
{
    protected ReportsService $reportsService;

    public function __construct(ReportsService $reportsService)
    {
        $this->reportsService = $reportsService;
    }

    /**
     * Generate daily report for a class
     */
    public function dailyReport(Request $request, int $classId): JsonResponse|Response
    {
        $request->validate([
            'date' => 'required|date',
            'export' => 'nullable|in:pdf,csv',
        ]);

        // Check authorization
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isTeacher()) {
            abort(403, 'Unauthorized to access reports');
        }

        $date = $request->date;
        $export = $request->export;

        try {
            $reportData = $this->reportsService->generateDailyReport($classId, $date);

            if ($export === 'csv') {
                $csv = $this->reportsService->exportDailyReportAsCsv($reportData);

                return response($csv)
                    ->header('Content-Type', 'text/csv; charset=UTF-8')
                    ->header(
                        'Content-Disposition',
                        'attachment; filename="daily_report_' . $classId . '_' . $date . '.csv"'
                    );
            }

            if ($export === 'pdf') {
                $pdf = Pdf::loadView('reports.daily', $reportData);
                $pdf->setPaper('A4', 'portrait');

                return $pdf->download('daily_report_' . $classId . '_' . $date . '.pdf');
            }

            return response()->json([
                'success' => true,
                'data' => $reportData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate daily report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate monthly report for a class
     */
    public function generateMonthlyReport(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'hijri_month' => 'nullable|integer|between:1,12',
            'hijri_year' => 'nullable|integer|min:1400',
        ]);

        // Check authorization
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isTeacher()) {
            abort(403, 'Unauthorized to access reports');
        }

        try {
            $reportData = $this->reportsService->generateMonthlyReport(
                $request->class_id,
                $request->month,
                $request->year,
                $request->hijri_month,
                $request->hijri_year
            );

            $filepath = $this->reportsService->storeMonthlyReport($reportData);

            return response()->json([
                'success' => true,
                'message' => 'Monthly report generated successfully',
                'data' => [
                    'report_id' => $filepath,
                    'class_name' => $reportData['class']->name,
                    'month_name' => $reportData['month_name'],
                    'hijri_month_name' => $reportData['hijri_month_name'],
                    'summary' => $reportData['summary'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate monthly report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export monthly report as PDF
     */
    public function exportMonthlyReport(string $reportId): Response
    {
        // Check authorization
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isTeacher()) {
            abort(403, 'Unauthorized to access reports');
        }

        try {
            // Debug: Log the report ID
            \Log::info('PDF export request for report ID: ' . $reportId);

            $reportData = $this->reportsService->getStoredMonthlyReport($reportId);

            $pdf = Pdf::loadView('reports.monthly', $reportData);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'monthly_report_' . $reportData['class']['id'] . '_' . $reportData['year'] . '_' .
                $reportData['month'] . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('PDF export error: ' . $e->getMessage());
            abort(404, 'Report not found: ' . $e->getMessage());
        }
    }
}
