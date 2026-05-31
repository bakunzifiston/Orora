<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MilkSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\MilkSaleItemRequest;
use App\Http\Requests\MilkSalePaymentRequest;
use App\Http\Requests\MilkSaleRequest;
use App\Models\Farm;
use App\Models\MilkSale;
use App\Models\MilkStorage;
use App\Services\MilkSaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MilkSaleController extends Controller
{
    use MilkSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(
        private readonly MilkSaleService $saleService,
    ) {}

    public function index(): View
    {
        $sales = MilkSale::query()
            ->with('farm')
            ->orderByDesc('sold_on')
            ->paginate(15);

        return view('modules.milk.sales.index', $this->milkSectionData('sales', compact('sales')));
    }

    public function create(): View
    {
        return view('modules.milk.sales.create', $this->milkSectionData('sales', $this->formOptions()));
    }

    public function store(MilkSaleRequest $request): RedirectResponse
    {
        try {
            $sale = $this->saleService->create(
                $request->saleAttributes(),
                $request->input('items', []),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['sale' => $e->getMessage()]);
        }

        return redirect()->route('milk.sales.edit', $sale)->with('success', 'Milk sale draft created.');
    }

    public function edit(MilkSale $milkSale): View
    {
        $milkSale->load(['farm', 'items.storage', 'payments', 'logs']);

        return view('modules.milk.sales.edit', $this->milkSectionData('sales', array_merge(
            $this->formOptions($milkSale->farm_id),
            ['milkSale' => $milkSale],
        )));
    }

    public function update(MilkSaleRequest $request, MilkSale $milkSale): RedirectResponse
    {
        if ($milkSale->status !== 'draft') {
            return back()->withErrors(['sale' => 'Only draft sales can be edited.']);
        }

        $milkSale->update($request->saleAttributes());

        return redirect()->route('milk.sales.edit', $milkSale)->with('success', 'Sale updated.');
    }

    public function confirm(MilkSale $milkSale): RedirectResponse
    {
        try {
            $this->saleService->confirm($milkSale);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['sale' => $e->getMessage()]);
        }

        return redirect()->route('milk.sales.edit', $milkSale)->with('success', 'Sale confirmed and stock deducted.');
    }

    public function storeItem(MilkSaleItemRequest $request, MilkSale $milkSale): RedirectResponse
    {
        try {
            $this->saleService->addItem($milkSale, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['sale' => $e->getMessage()]);
        }

        return redirect()->route('milk.sales.edit', $milkSale)->with('success', 'Line item added.');
    }

    public function storePayment(MilkSalePaymentRequest $request, MilkSale $milkSale): RedirectResponse
    {
        if ($milkSale->status === 'cancelled') {
            return back()->withErrors(['payment' => 'Cannot record payments on a cancelled sale.']);
        }

        $this->saleService->addPayment($milkSale, $request->validated());

        return redirect()->route('milk.sales.edit', $milkSale)->with('success', 'Payment recorded.');
    }

    public function destroy(MilkSale $milkSale): RedirectResponse
    {
        if ($milkSale->status === 'confirmed') {
            return back()->withErrors(['sale' => 'Confirmed sales cannot be deleted.']);
        }

        $milkSale->delete();

        return redirect()->route('milk.sales')->with('success', 'Sale removed.');
    }

    private function formOptions(?int $farmId = null): array
    {
        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'storageTanks' => MilkStorage::query()
                ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
                ->orderBy('container_name')
                ->get(),
        ];
    }
}
