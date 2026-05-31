<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerCreditRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;

class CustomerCreditController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    public function update(CustomerCreditRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->updateCredit($customer, $request->validated());

        return redirect()->route('customers.show', $customer)->with('success', 'Credit settings updated.');
    }
}
