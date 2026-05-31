<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\Livestock;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    use ProvidesModuleNavigation;

    public function index(): View
    {
        $sales = Sale::query()->with(['farm', 'animal', 'livestock'])->orderByDesc('sold_on')->paginate(15);

        return view('modules.sales.index', $this->moduleViewData('sales', compact('sales')));
    }

    public function create(): View
    {
        return view('modules.sales.create', $this->moduleViewData('sales', $this->formOptions()));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['total_amount'] = $this->resolveTotal($validated);

        Sale::create($validated);

        return redirect()->route('sales.index')->with('success', 'Sale recorded successfully.');
    }

    public function edit(Sale $sale): View
    {
        return view('modules.sales.edit', $this->moduleViewData('sales', array_merge($this->formOptions(), compact('sale'))));
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['total_amount'] = $this->resolveTotal($validated);

        $sale->update($validated);

        return redirect()->route('sales.index')->with('success', 'Sale updated successfully.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $sale->delete();

        return redirect()->route('sales.index')->with('success', 'Sale removed successfully.');
    }

    private function formOptions(): array
    {
        return [
            'farms' => Farm::query()->orderBy('name')->get(),
            'animals' => Animal::query()->orderBy('tag_number')->get(),
            'livestockGroups' => Livestock::query()->orderBy('name')->get(),
        ];
    }

    private function rules(): array
    {
        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'product_type' => ['nullable', 'string', 'max:50'],
            'animal_id' => ['nullable', 'exists:animals,id'],
            'livestock_id' => ['nullable', 'exists:livestock,id'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_contact' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'sold_on' => ['required', 'date'],
            'payment_status' => ['required', 'in:'.implode(',', config('modules.payment_statuses'))],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function resolveTotal(array $validated): ?float
    {
        if (! empty($validated['total_amount'])) {
            return (float) $validated['total_amount'];
        }

        if (! empty($validated['unit_price'])) {
            return (float) $validated['unit_price'] * (int) $validated['quantity'];
        }

        return null;
    }
}
