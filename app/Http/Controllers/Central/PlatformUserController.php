<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\User;
use App\Services\PlatformUserWorkspaceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class PlatformUserController extends Controller
{
    public function __construct(
        private readonly PlatformUserWorkspaceService $workspace,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'all');

        if (! in_array($status, ['all', 'with_farm', 'pending'], true)) {
            $status = 'all';
        }

        if (! Schema::hasTable('users')) {
            return view('central.accounts.index', [
                'activeNav' => 'accounts',
                'usersReady' => false,
                'users' => collect(),
                'search' => $search,
                'status' => $status,
                'filtersActive' => false,
                'stats' => $this->emptyStats(),
            ]);
        }

        $base = User::query()->withoutGlobalScope('tenant');
        $stats = [
            'total' => (clone $base)->count(),
            'with_farm' => (clone $base)->where($this->hasFarm(...))->count(),
            'pending' => (clone $base)->where($this->missingFarm(...))->count(),
        ];

        $query = User::query()
            ->withoutGlobalScope('tenant')
            ->with('tenant')
            ->withCount([
                'farms as farms_count' => fn ($farmQuery) => $farmQuery->withoutGlobalScope('tenant'),
            ]);

        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(function (Builder $builder) use ($like) {
                $builder
                    ->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('tenant_id', 'like', $like);
            });
        }

        if ($status === 'with_farm') {
            $query->where($this->hasFarm(...));
        } elseif ($status === 'pending') {
            $query->where($this->missingFarm(...));
        }

        $users = $query
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('central.accounts.index', [
            'activeNav' => 'accounts',
            'usersReady' => true,
            'users' => $users,
            'search' => $search,
            'status' => $status,
            'filtersActive' => $search !== '' || $status !== 'all',
            'stats' => $stats,
        ]);
    }

    public function show(int $user): View
    {
        $user = User::query()
            ->withoutGlobalScope('tenant')
            ->with('tenant')
            ->findOrFail($user);

        $related = $this->workspace->relatedCounts($user);
        $relatedTotal = collect($related)->sum('count');

        $farms = Schema::hasTable('farms')
            ? Farm::query()
                ->withoutGlobalScope('tenant')
                ->where('tenant_id', $user->tenant_id)
                ->withCount(['livestock', 'animals'])
                ->orderBy('name')
                ->get()
            : collect();

        return view('central.accounts.show', [
            'activeNav' => 'accounts',
            'user' => $user,
            'farms' => $farms,
            'related' => $related,
            'relatedTotal' => $relatedTotal,
        ]);
    }

    public function destroy(int $user): RedirectResponse
    {
        $user = User::query()
            ->withoutGlobalScope('tenant')
            ->findOrFail($user);

        $name = $user->name;

        try {
            $this->workspace->purge($user);
        } catch (Throwable) {
            return back()->with('error', "Could not delete {$name}. Some related records could not be removed.");
        }

        return redirect()
            ->route('central.accounts.index')
            ->with('success', "{$name} and all related records were deleted.");
    }

    /**
     * @return array{total: int, with_farm: int, pending: int}
     */
    private function emptyStats(): array
    {
        return [
            'total' => 0,
            'with_farm' => 0,
            'pending' => 0,
        ];
    }

    private function hasFarm(Builder $query): void
    {
        $query->whereExists(function ($subquery): void {
            $subquery->selectRaw('1')
                ->from('farms')
                ->whereColumn('farms.tenant_id', 'users.tenant_id');
        });
    }

    private function missingFarm(Builder $query): void
    {
        $query->whereNotExists(function ($subquery): void {
            $subquery->selectRaw('1')
                ->from('farms')
                ->whereColumn('farms.tenant_id', 'users.tenant_id');
        });
    }
}
