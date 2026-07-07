<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Tenant;
use App\Models\TenantAccount;
use App\Models\User;
use App\Services\AdminPlatformStatsService;
use App\Services\AdminUserDirectoryFilterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class UserDirectoryController extends Controller
{
    public function __construct(
        private readonly AdminUserDirectoryFilterService $filters,
        private readonly AdminPlatformStatsService $stats,
    ) {}

    public function index(Request $request): View
    {
        $filtersActive = $this->filters->filtersActive($request);
        $filters = $filtersActive ? $this->filters->resolve($request) : $this->filters->defaults();
        $rangeStart = $filtersActive
            ? $this->filters->rangeStart($filters)
            : Carbon::parse('2000-01-01')->startOfDay();
        $rangeEnd = $filtersActive
            ? $this->filters->rangeEnd($filters)
            : now()->endOfDay();
        $farmIds = $filtersActive ? $this->filters->scopedFarmIds($filters) : null;

        if (! Schema::hasTable('farms')) {
            return view('central.users.index', [
                'activeNav' => 'users',
                'filters' => $filters,
                'filtersActive' => false,
                'farmsReady' => false,
                'farms' => collect(),
                'stats' => $this->emptyPlatformStats(),
                'accountEmails' => collect(),
                'tenantNames' => collect(),
                'farmOptions' => collect(),
                'provinces' => [],
                'districts' => [],
            ]);
        }

        $farmsQuery = Farm::query()
            ->withoutGlobalScope('tenant')
            ->withCount(['livestock', 'animals']);

        if ($filtersActive) {
            $this->filters->applyToFarms($farmsQuery, $filters);
        }

        $farms = $farmsQuery
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $tenantIds = $farms->pluck('tenant_id')->unique()->filter();

        $accountEmails = TenantAccount::query()
            ->whereIn('tenant_id', $tenantIds)
            ->pluck('email', 'tenant_id');

        $tenantNames = Tenant::query()
            ->whereIn('id', $tenantIds)
            ->pluck('name', 'id');

        $statsFilters = [
            'from' => $rangeStart->toDateString(),
            'to' => $rangeEnd->toDateString(),
        ];

        $totalFarms = Farm::query()->withoutGlobalScope('tenant')->count();

        return view('central.users.index', [
            'activeNav' => 'users',
            'filters' => $filters,
            'filtersActive' => $filtersActive,
            'farmsReady' => true,
            'farms' => $farms,
            'totalFarms' => $totalFarms,
            'stats' => $this->stats->platformStats($statsFilters, $rangeStart, $rangeEnd, $farmIds),
            'accountEmails' => $accountEmails,
            'tenantNames' => $tenantNames,
            'farmOptions' => Farm::query()->withoutGlobalScope('tenant')->orderBy('name')->get(['id', 'name', 'district', 'province']),
            'provinces' => $this->filters->provinces(),
            'districts' => $filtersActive
                ? $this->filters->districts($filters['province_code'] ?? null)
                : [],
        ]);
    }

    public function show(Request $request, int $farm): View
    {
        $farm = Farm::query()->withoutGlobalScope('tenant')->findOrFail($farm);

        $filtersActive = $this->filters->filtersActive($request);
        $filters = $filtersActive ? $this->filters->resolve($request) : $this->filters->defaults();
        $rangeStart = $filtersActive
            ? $this->filters->rangeStart($filters)
            : Carbon::parse('2000-01-01')->startOfDay();
        $rangeEnd = $filtersActive
            ? $this->filters->rangeEnd($filters)
            : now()->endOfDay();

        $farm->load('members');

        $farmStats = $this->stats->farmStats($farm, [
            'from' => $rangeStart->toDateString(),
            'to' => $rangeEnd->toDateString(),
        ], $rangeStart, $rangeEnd);

        $livestockGroups = Schema::hasTable('livestock')
            ? $farm->livestock()
                ->withCount(['animals as animals_count' => fn ($query) => $filtersActive
                    ? $query->whereBetween('created_at', [$rangeStart, $rangeEnd])
                    : $query])
                ->when($filtersActive, fn ($query) => $query->whereBetween('created_at', [$rangeStart, $rangeEnd]))
                ->orderBy('name')
                ->get()
            : collect();

        $workspaceAccount = TenantAccount::query()
            ->where('tenant_id', $farm->tenant_id)
            ->value('email');

        $workspaceUser = Schema::hasTable('users')
            ? User::query()->where('tenant_id', $farm->tenant_id)->orderBy('id')->first()
            : null;

        $tenant = Tenant::query()->find($farm->tenant_id);

        return view('central.users.show', [
            'activeNav' => 'users',
            'filters' => $filters,
            'filtersActive' => $filtersActive,
            'farm' => $farm,
            'stats' => $farmStats,
            'livestockGroups' => $livestockGroups,
            'workspaceAccount' => $workspaceAccount,
            'workspaceUser' => $workspaceUser,
            'tenant' => $tenant,
        ]);
    }

    /**
     * @return array{
     *     accounts_without_farm: int,
     *     users_with_farm: int,
     *     farms: int,
     *     livestock_groups: int,
     *     head_count: int,
     *     animals: int,
     *     liter_yield: float,
     *     liters_sold: float
     * }
     */
    private function emptyPlatformStats(): array
    {
        return [
            'accounts_without_farm' => 0,
            'users_with_farm' => 0,
            'farms' => 0,
            'livestock_groups' => 0,
            'head_count' => 0,
            'animals' => 0,
            'liter_yield' => 0.0,
            'liters_sold' => 0.0,
        ];
    }
}
