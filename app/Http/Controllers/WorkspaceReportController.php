<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Farm;
use App\Services\ActivityReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceReportController extends Controller
{
    use ProvidesModuleNavigation;

    private const PDF_ROW_LIMIT = 3000;

    public function __construct(private readonly ActivityReportService $reports) {}

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $report = $this->reports->build($filters);

        return view('workspace.reports.index', $this->moduleViewData('workspace-reports', [
            'filters' => $filters,
            'summary' => $report['summary'],
            'rows' => $report['rows']->take(100),
            'totalRows' => $report['rows']->count(),
            'truncated' => $report['truncated'],
            'farms' => Farm::query()->orderBy('name')->get(),
            'moduleOptions' => ActivityReportService::MODULES,
        ]));
    }

    public function pdf(Request $request)
    {
        $filters = $this->filters($request);
        $report = $this->reports->build($filters);
        $rows = $report['rows'];
        $cut = $rows->count() > self::PDF_ROW_LIMIT;

        $farm = $filters['farm_id'] ? Farm::query()->find($filters['farm_id'])?->name : null;

        $pdf = Pdf::loadView('workspace.reports.pdf', [
            'filters' => $filters,
            'farmName' => $farm ?? __('All farms'),
            'moduleLabels' => collect($filters['modules'])->map(fn ($k) => __(ActivityReportService::MODULES[$k]))->implode(', '),
            'summary' => $report['summary'],
            'sales' => $report['sales'],
            'rows' => $rows->take(self::PDF_ROW_LIMIT),
            'truncated' => $report['truncated'] || $cut,
            'generatedAt' => now(),
            'generatedBy' => $request->user()?->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('activity-report-'.$filters['from'].'_to_'.$filters['to'].'.pdf');
    }

    /** @return array{from:string,to:string,farm_id:?int,modules:list<string>} */
    private function filters(Request $request): array
    {
        $keys = array_keys(ActivityReportService::MODULES);

        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'farm_id' => ['nullable', 'integer'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['in:'.implode(',', $keys)],
        ]);

        return [
            'from' => $data['from'] ?? now()->startOfMonth()->toDateString(),
            'to' => $data['to'] ?? now()->toDateString(),
            'farm_id' => filled($data['farm_id'] ?? null) ? (int) $data['farm_id'] : null,
            'modules' => ! empty($data['modules']) ? array_values($data['modules']) : $keys,
        ];
    }
}
