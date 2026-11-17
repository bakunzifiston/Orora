<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Controllers\Concerns\SalesSectionViews;
use App\Models\Customer;
use App\Models\Farm;
use App\Services\SalesReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    use ProvidesModuleNavigation;
    use SalesSectionViews;

    public function __construct(private readonly SalesReportService $reports) {}

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $query = $this->reports->query($filters);

        $summary = $this->reports->summarize((clone $query)->get());
        $preview = (clone $query)->limit(50)->get();

        return view('modules.sales.reports.index', $this->salesSectionData('reports', [
            'filters' => $filters,
            'summary' => $summary,
            'preview' => $preview,
            'previewTruncated' => $summary->sum('count') > $preview->count(),
            'farms' => Farm::query()->orderBy('name')->get(),
            'customers' => Customer::query()->get()->sortBy('display_name', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'saleTypes' => app(\App\Services\Species\SpeciesProfile::class)->workspaceSaleTypes(),
        ]));
    }

    public function pdf(Request $request)
    {
        $filters = $this->filters($request);
        $sales = $this->reports->query($filters)->limit(SalesReportService::PDF_ROW_LIMIT)->get();

        $pdf = Pdf::loadView('modules.sales.reports.pdf', [
            'sales' => $sales,
            'summary' => $this->reports->summarize($sales),
            'filterLabels' => $this->filterLabels($filters),
            'generatedAt' => now(),
            'generatedBy' => $request->user()?->name,
            'truncated' => $sales->count() >= SalesReportService::PDF_ROW_LIMIT,
            'reports' => $this->reports,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('sales-report-'.now()->format('Ymd-His').'.pdf');
    }

    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'farm_id' => ['nullable', 'integer'],
            'customer_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:'.implode(',', config('modules.sale_types'))],
            'status' => ['nullable', 'in:'.implode(',', config('modules.sale_statuses'))],
            'payment_status' => ['nullable', 'in:'.implode(',', config('modules.sale_payment_statuses'))],
        ]);

        return array_filter($data, fn ($v) => $v !== null && $v !== '');
    }

    /** @return array<string, string> label => value shown on the PDF */
    private function filterLabels(array $filters): array
    {
        $labels = [];
        $labels[__('Period')] = ($filters['from'] ?? null) || ($filters['to'] ?? null)
            ? ($filters['from'] ?? __('start')).' → '.($filters['to'] ?? __('today'))
            : __('All dates');
        $labels[__('Farm')] = isset($filters['farm_id']) ? (Farm::query()->find($filters['farm_id'])?->name ?? __('Unknown')) : __('All farms');
        $labels[__('Customer')] = isset($filters['customer_id']) ? (Customer::query()->find($filters['customer_id'])?->display_name ?? __('Unknown')) : __('All customers');
        $labels[__('Type')] = isset($filters['type']) ? __(config('modules.sale_type_labels.'.$filters['type'])) : __('All types');
        $labels[__('Status')] = isset($filters['status']) ? __(config('modules.sale_status_labels.'.$filters['status'], Str::headline($filters['status']))) : __('All statuses');
        $labels[__('Payment')] = isset($filters['payment_status']) ? __(config('modules.sale_payment_status_labels.'.$filters['payment_status'], Str::headline($filters['payment_status']))) : __('All payment statuses');

        return $labels;
    }
}
