<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CustomerSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Customer;
use App\Models\SaleTransaction;
use Illuminate\View\View;

class CustomerModuleController extends Controller
{
    use CustomerSectionViews;
    use ProvidesModuleNavigation;

    public function overview(): View
    {
        $stats = [
            'total' => Customer::query()->count(),
            'active' => Customer::query()->where('status', 'active')->count(),
            'outstanding' => (float) Customer::query()
                ->join('customer_credit', 'customers.id', '=', 'customer_credit.customer_id')
                ->sum('customer_credit.outstanding_balance'),
            'over_limit' => Customer::query()
                ->join('customer_credit', 'customers.id', '=', 'customer_credit.customer_id')
                ->whereColumn('customer_credit.outstanding_balance', '>', 'customer_credit.credit_limit')
                ->where('customer_credit.credit_limit', '>', 0)
                ->count(),
            'sales_month' => SaleTransaction::query()
                ->whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->whereNotIn('sale_status', ['cancelled', 'draft'])
                ->sum('total_amount'),
        ];

        $recentCustomers = Customer::query()
            ->with('credit')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $topCustomers = Customer::query()
            ->withSum(['saleTransactions as lifetime_sales' => fn ($q) => $q->whereNotIn('sale_status', ['cancelled', 'draft'])], 'total_amount')
            ->orderByDesc('lifetime_sales')
            ->limit(6)
            ->get();

        return view('modules.customers.overview', $this->customerSectionData('overview', compact('stats', 'recentCustomers', 'topCustomers')));
    }
}
