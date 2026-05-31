<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerContactRequest;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;

class CustomerContactController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    public function store(CustomerContactRequest $request, Customer $customer): RedirectResponse
    {
        if ($request->boolean('is_primary')) {
            $customer->contacts()->update(['is_primary' => false]);
        }

        $customer->contacts()->create($request->validated());
        $this->customerService->log($customer, 'updated', null, null, 'Contact added.');

        return redirect()->route('customers.show', $customer)->with('success', 'Contact added.');
    }

    public function update(CustomerContactRequest $request, Customer $customer, CustomerContact $contact): RedirectResponse
    {
        if ($request->boolean('is_primary')) {
            $customer->contacts()->whereKeyNot($contact->id)->update(['is_primary' => false]);
        }

        $contact->update($request->validated());
        $this->customerService->log($customer, 'updated', null, null, 'Contact updated.');

        return redirect()->route('customers.show', $customer)->with('success', 'Contact updated.');
    }

    public function destroy(Customer $customer, CustomerContact $contact): RedirectResponse
    {
        $contact->delete();
        $this->customerService->log($customer, 'updated', null, null, 'Contact removed.');

        return redirect()->route('customers.show', $customer)->with('success', 'Contact removed.');
    }
}
