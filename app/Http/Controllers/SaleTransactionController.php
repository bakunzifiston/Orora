<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Controllers\Concerns\SalesSectionViews;
use App\Http\Requests\SaleItemRequest;
use App\Http\Requests\SalePaymentRequest;
use App\Http\Requests\SaleTransactionRequest;
use App\Models\Animal;
use App\Models\Customer;
use App\Models\Farm;
use App\Models\MilkStorage;
use App\Models\SaleTransaction;
use App\Services\CustomerService;
use App\Services\SaleTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleTransactionController extends Controller
{
    use ProvidesModuleNavigation;
    use SalesSectionViews;

    public function __construct(
        private readonly SaleTransactionService $saleService,
        private readonly CustomerService $customerService,
    ) {}

    public function index(Request $request): View
    {
        $transactions = SaleTransaction::query()
            ->with(['farm', 'customer'])
            ->when($request->filled('type'), fn ($q) => $q->where('sale_type', $request->input('type')))
            ->when($request->filled('farm_id'), fn ($q) => $q->where('farm_id', $request->integer('farm_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('sale_status', $request->input('status')))
            ->orderByDesc('sale_date')
            ->paginate(15)
            ->withQueryString();

        return view('modules.sales.transactions.index', $this->salesSectionData('transactions', [
            'transactions' => $transactions,
            'farms' => Farm::query()->orderBy('name')->get(),
            'filterType' => $request->input('type'),
            'filterFarmId' => $request->input('farm_id'),
            'filterStatus' => $request->input('status'),
        ]));
    }

    public function create(Request $request): View
    {
        $type = $request->input('type', 'animal_sale');

        return view('modules.sales.transactions.create', $this->salesSectionData('transactions', array_merge(
            $this->formOptions($request->integer('farm_id') ?: null),
            [
                'saleType' => $type,
                'preselectedCustomerId' => $request->integer('customer_id') ?: null,
            ],
        )));
    }

    public function store(SaleTransactionRequest $request): RedirectResponse
    {
        try {
            $attributes = $request->headerAttributes();
            $attributes['customer_id'] = $this->resolveCustomerId($request);
            $transaction = $this->saleService->createDraft($attributes);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['sale' => $e->getMessage()]);
        }

        $message = $request->isNewCustomerMode()
            ? 'Customer registered. Add what was sold below, then confirm the sale.'
            : 'Add line items below, then confirm the sale.';

        return redirect()->route('sales.transactions.show', $transaction)->with('success', $message);
    }

    public function show(SaleTransaction $transaction): View
    {
        $transaction->load(['farm', 'customer', 'items.animal', 'items.milkStorage', 'items.customer', 'payments', 'logs.actor']);

        return view('modules.sales.transactions.show', $this->salesSectionData('transactions', array_merge(
            $this->formOptions($transaction->farm_id, $transaction),
            ['transaction' => $transaction],
        )));
    }

    public function update(SaleTransactionRequest $request, SaleTransaction $transaction): RedirectResponse
    {
        if ($transaction->sale_status !== 'draft') {
            return back()->withErrors(['sale' => 'Only draft sales can be edited.']);
        }

        $transaction->update([
            ...$request->headerAttributes(),
            'customer_id' => $this->resolveCustomerId($request, $transaction),
        ]);
        $this->saleService->recalculateTotals($transaction);

        return redirect()->route('sales.transactions.show', $transaction)->with('success', 'Sale updated.');
    }

    public function storeItem(SaleItemRequest $request, SaleTransaction $transaction): RedirectResponse
    {
        try {
            $this->saleService->addItem($transaction, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['item' => $e->getMessage()]);
        }

        return redirect()->route('sales.transactions.show', $transaction)->with('success', 'Line item added.');
    }

    public function storePayment(SalePaymentRequest $request, SaleTransaction $transaction): RedirectResponse
    {
        try {
            $this->saleService->addPayment($transaction, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return redirect()->route('sales.transactions.show', $transaction)->with('success', 'Payment recorded.');
    }

    public function confirm(SaleTransaction $transaction): RedirectResponse
    {
        try {
            if ($transaction->sale_type === 'milk_sale') {
                $this->saleService->confirm($transaction);
            } else {
                $this->saleService->complete($transaction);
            }
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['sale' => $e->getMessage()]);
        }

        return redirect()->route('sales.transactions.show', $transaction)->with('success', 'Sale confirmed.');
    }

    public function complete(SaleTransaction $transaction): RedirectResponse
    {
        try {
            $this->saleService->complete($transaction);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['sale' => $e->getMessage()]);
        }

        return redirect()->route('sales.transactions.show', $transaction)->with('success', 'Sale completed.');
    }

    public function cancel(SaleTransaction $transaction): RedirectResponse
    {
        try {
            $this->saleService->cancel($transaction);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['sale' => $e->getMessage()]);
        }

        return redirect()->route('sales.transactions.show', $transaction)->with('success', 'Sale cancelled.');
    }

    public function destroy(SaleTransaction $transaction): RedirectResponse
    {
        if (! in_array($transaction->sale_status, ['draft', 'cancelled'], true)) {
            return back()->withErrors(['sale' => 'Only draft or cancelled sales can be deleted.']);
        }

        $transaction->delete();

        return redirect()->route('sales.transactions')->with('success', 'Sale removed.');
    }

    private function formOptions(?int $farmId = null, ?SaleTransaction $transaction = null): array
    {
        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'customers' => Customer::query()
                ->where('status', 'active')
                ->orderBy('display_name')
                ->get(),
            'animals' => Animal::query()
                ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
                ->where('lifecycle_status', 'Active')
                ->orderBy('tag_number')
                ->get(),
            'storageTanks' => MilkStorage::query()
                ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
                ->orderBy('container_name')
                ->get(),
            'transaction' => $transaction,
        ];
    }

    private function resolveCustomerId(SaleTransactionRequest $request, ?SaleTransaction $transaction = null): ?int
    {
        if ($request->input('customer_mode') === 'none') {
            return null;
        }

        if ($request->isNewCustomerMode()) {
            if ($transaction?->customer_id) {
                return (int) $transaction->customer_id;
            }

            $type = $request->input('new_customer_type', 'individual');
            $displayName = $request->input('new_customer_display_name');

            $profile = $type === 'individual'
                ? [
                    'first_name' => $this->firstNameFrom($displayName),
                    'last_name' => $this->lastNameFrom($displayName),
                ]
                : ['organization_name' => $displayName];

            $contact = null;
            if ($request->filled('new_customer_phone') || $request->filled('new_customer_email')) {
                $contact = [
                    'contact_name' => $displayName,
                    'phone' => $request->input('new_customer_phone'),
                    'email' => $request->input('new_customer_email'),
                ];
            }

            $customer = $this->customerService->create(
                [
                    'customer_type' => $type,
                    'display_name' => $displayName,
                    'status' => 'active',
                    'trust_level' => 'new',
                    'currency' => $request->input('currency', 'RWF'),
                ],
                $profile,
                $contact,
            );

            return $customer->id;
        }

        $id = $request->integer('customer_id');

        return $id > 0 ? $id : null;
    }

    private function firstNameFrom(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return $parts[0] ?? $name;
    }

    private function lastNameFrom(string $name): ?string
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return $parts[1] ?? null;
    }
}
