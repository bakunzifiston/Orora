<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MilkSectionViews;
use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\MilkDistributionRequest;
use App\Models\Animal;
use App\Models\Farm;
use App\Models\MilkDistribution;
use App\Models\MilkStorage;
use App\Services\Milk\MilkDistributionService;
use App\Services\Species\SpeciesProfile;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use InvalidArgumentException;

class MilkDistributionController extends Controller
{
    use MilkSectionViews;
    use ProvidesModuleNavigation;

    public function __construct(
        private readonly MilkDistributionService $distributions,
        private readonly SpeciesProfile $species,
    ) {}

    public function index(Request $request): View
    {
        $farmId = $request->filled('farm_id') ? $request->integer('farm_id') : null;
        $category = in_array($request->input('category'), config('modules.milk_distribution_categories'), true)
            ? $request->input('category')
            : null;
        $period = in_array($request->input('period'), ['today', 'monthly', 'yearly', 'all'], true)
            ? $request->input('period')
            : 'monthly';
        [$from, $to, $periodLabel] = $this->periodRange($period);

        $distributions = MilkDistribution::query()
            ->with(['farm', 'animal', 'storage', 'recorder', 'saleTransaction'])
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($from && $to, fn ($q) => $q->whereBetween('distributed_at', [$from, $to]))
            ->orderByDesc('distributed_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $totals = $this->distributions->totalsByCategory($farmId, $from, $to);

        return view('modules.milk.distribution.index', $this->milkSectionData('distribution', [
            'distributions' => $distributions,
            'totals' => $totals,
            'farms' => $this->milkFarms(),
            'selectedFarm' => $farmId,
            'selectedCategory' => $category,
            'period' => $period,
            'periodLabel' => $periodLabel,
        ]));
    }

    public function create(Request $request): View
    {
        $category = in_array($request->input('category'), config('modules.milk_distribution_manual_categories'), true)
            ? $request->input('category')
            : 'fed_to_calf';

        return view('modules.milk.distribution.create', $this->milkSectionData('distribution', [
            ...$this->formData(),
            'defaultCategory' => $category,
        ]));
    }

    public function store(MilkDistributionRequest $request): RedirectResponse
    {
        try {
            $this->distributions->create($request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['quantity_liters' => $e->getMessage()]);
        }

        return redirect()->route('milk.distribution')->with('success', __('Milk distribution recorded.'));
    }

    public function edit(MilkDistribution $milkDistribution): View|RedirectResponse
    {
        if ($milkDistribution->isFromSale()) {
            return $this->redirectToSale($milkDistribution);
        }

        return view('modules.milk.distribution.edit', $this->milkSectionData('distribution', [
            ...$this->formData($milkDistribution),
            'milkDistribution' => $milkDistribution,
            'defaultCategory' => $milkDistribution->category,
        ]));
    }

    public function update(MilkDistributionRequest $request, MilkDistribution $milkDistribution): RedirectResponse
    {
        if ($milkDistribution->isFromSale()) {
            return $this->redirectToSale($milkDistribution);
        }

        try {
            $this->distributions->update($milkDistribution, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['quantity_liters' => $e->getMessage()]);
        }

        return redirect()->route('milk.distribution')->with('success', __('Milk distribution updated.'));
    }

    public function destroy(MilkDistribution $milkDistribution): RedirectResponse
    {
        if ($milkDistribution->isFromSale()) {
            return $this->redirectToSale($milkDistribution);
        }

        try {
            $this->distributions->delete($milkDistribution);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['distribution' => $e->getMessage()]);
        }

        return redirect()->route('milk.distribution')->with('success', __('Milk distribution removed.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?MilkDistribution $current = null): array
    {
        $farms = $this->milkFarms();
        $farmIds = $farms->pluck('id');

        $calves = Animal::query()
            ->whereIn('farm_id', $farmIds)
            ->calfFeedingEligible()
            ->orderBy('tag_number')
            ->get(['id', 'farm_id', 'tag_number', 'name', 'date_of_birth']);

        // Keep a previously selected calf visible even if it has since grown out of eligibility.
        if ($current?->animal_id && ! $calves->contains('id', $current->animal_id) && $current->animal) {
            $calves->push($current->animal);
        }

        return [
            'farms' => $farms,
            'calves' => $calves,
            'storageTanks' => MilkStorage::query()
                ->whereIn('farm_id', $farmIds)
                ->where('status', '!=', 'maintenance')
                ->orderBy('container_name')
                ->get(['id', 'farm_id', 'container_name', 'current_quantity_liters']),
            'categoryLabels' => collect(config('modules.milk_distribution_category_labels'))
                ->only(config('modules.milk_distribution_manual_categories'))
                ->all(),
            'recipientTypeLabels' => config('modules.milk_distribution_recipient_type_labels'),
            'categoryRecipients' => config('modules.milk_distribution_category_recipients'),
            'calfMaxAgeMonths' => (int) config('modules.milk_calf_max_age_months', 12),
        ];
    }

    /**
     * Farms whose species profile includes the milk module.
     */
    private function milkFarms(): Collection
    {
        return Farm::query()
            ->orderBy('name')
            ->get(['id', 'name', 'primary_species'])
            ->filter(fn (Farm $farm) => $this->species->allows('milk', $farm))
            ->values();
    }

    private function redirectToSale(MilkDistribution $distribution): RedirectResponse
    {
        $route = $distribution->sale_transaction_id && Route::has('sales.transactions.show')
            ? route('sales.transactions.show', $distribution->sale_transaction_id)
            : route('milk.distribution');

        return redirect($route)->with('success', __('Sold milk is managed from the Sales module.'));
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: string}
     */
    private function periodRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay(), __('Today')],
            'monthly' => [now()->startOfMonth(), now()->endOfMonth(), __('This month')],
            'yearly' => [now()->startOfYear(), now()->endOfYear(), __('This year')],
            default => [null, null, __('All time')],
        };
    }
}
