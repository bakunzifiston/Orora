<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerAddressRequest;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;

class CustomerAddressController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    public function store(CustomerAddressRequest $request, Customer $customer): RedirectResponse
    {
        if ($request->boolean('is_default')) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $customer->addresses()->create($request->validated());
        $this->customerService->log($customer, 'updated', null, null, 'Address added.');

        return redirect()->route('customers.show', $customer)->with('success', 'Address added.');
    }

    public function destroy(Customer $customer, CustomerAddress $address): RedirectResponse
    {
        $address->delete();
        $this->customerService->log($customer, 'updated', null, null, 'Address removed.');

        return redirect()->route('customers.show', $customer)->with('success', 'Address removed.');
    }
}
