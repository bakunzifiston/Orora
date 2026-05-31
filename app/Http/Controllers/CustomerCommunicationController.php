<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CustomerSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\CustomerCommunicationRequest;
use App\Models\Customer;
use App\Models\CustomerCommunication;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerCommunicationController extends Controller
{
    use CustomerSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(private readonly CustomerService $customerService) {}

    public function index(Request $request): View
    {
        $communications = CustomerCommunication::query()
            ->with(['customer', 'logger'])
            ->when($request->filled('type'), fn ($q) => $q->where('communication_type', $request->input('type')))
            ->when($request->boolean('follow_up'), fn ($q) => $q->where('follow_up_required', true)->whereDate('follow_up_date', '>=', now()))
            ->orderByDesc('communication_date')
            ->paginate(20)
            ->withQueryString();

        return view('modules.customers.communications.index', $this->customerSectionData('communications', [
            'communications' => $communications,
            'filterType' => $request->input('type'),
            'filterFollowUp' => $request->boolean('follow_up'),
        ]));
    }

    public function store(CustomerCommunicationRequest $request, Customer $customer): RedirectResponse
    {
        $customer->communications()->create([
            ...$request->validated(),
            'logged_by' => auth()->id(),
        ]);

        $this->customerService->log($customer, 'updated', null, null, 'Communication logged.');

        return redirect()->route('customers.show', $customer)->with('success', 'Communication logged.');
    }
}
