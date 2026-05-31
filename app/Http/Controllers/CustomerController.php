<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CustomerSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    use CustomerSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private readonly CustomerService $customerService) {}

    public function directory(Request $request): View
    {
        $customers = Customer::query()
            ->with('credit')
            ->withCount('saleTransactions')
            ->when($request->filled('type'), fn ($q) => $q->where('customer_type', $request->input('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($query) use ($request) {
                $term = '%'.$request->input('q').'%';
                $query->where('display_name', 'like', $term)
                    ->orWhere('customer_code', 'like', $term);
            }))
            ->orderBy('display_name')
            ->paginate(15)
            ->withQueryString();

        return view('modules.customers.directory', $this->customerSectionData('directory', [
            'customers' => $customers,
            'filterType' => $request->input('type'),
            'filterStatus' => $request->input('status'),
            'filterQuery' => $request->input('q'),
        ]));
    }

    public function create(): View
    {
        return view('modules.customers.create', $this->customerSectionData('directory'));
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $customer = $this->customerService->create(
            $request->customerAttributes(),
            $request->profileAttributes(),
            $request->primaryContactAttributes(),
        );

        return redirect()->route('customers.show', $customer)->with('success', 'Customer registered successfully.');
    }

    public function show(Customer $customer): View
    {
        $this->customerService->syncOutstandingBalance($customer);

        $customer->load([
            'profile',
            'credit',
            'contacts',
            'addresses',
            'documents.uploader',
            'communications.logger',
            'logs.actor',
            'saleTransactions' => fn ($q) => $q->with('farm')->orderByDesc('sale_date')->limit(20),
        ]);

        $purchaseStats = [
            'total_sales' => $customer->saleTransactions()->whereNotIn('sale_status', ['cancelled', 'draft'])->count(),
            'lifetime_value' => (float) $customer->saleTransactions()->whereNotIn('sale_status', ['cancelled', 'draft'])->sum('total_amount'),
            'by_type' => $customer->saleTransactions()
                ->whereNotIn('sale_status', ['cancelled', 'draft'])
                ->selectRaw('sale_type, SUM(total_amount) as total')
                ->groupBy('sale_type')
                ->pluck('total', 'sale_type'),
        ];

        return view('modules.customers.show', $this->customerSectionData('directory', compact('customer', 'purchaseStats')));
    }

    public function edit(Customer $customer): View
    {
        $customer->load(['profile', 'contacts']);

        return view('modules.customers.edit', $this->customerSectionData('directory', compact('customer')));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->update(
            $customer,
            $request->customerAttributes(),
            $request->profileAttributes(),
        );

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->saleTransactions()->exists()) {
            return redirect()->route('customers.directory')->with('error', 'Customer has sales history and cannot be deleted.');
        }

        $customer->delete();

        return redirect()->route('customers.directory')->with('success', 'Customer removed successfully.');
    }
}
