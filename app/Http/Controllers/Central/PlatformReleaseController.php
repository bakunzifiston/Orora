<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\PlatformReleaseRequest;
use App\Models\Central\AdminUser;
use App\Models\Central\PlatformRelease;
use App\Models\Tenant;
use App\Services\Releases\ReleasePublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Release notes for tenants. Any platform admin can draft; only super admins
 * can publish (which notifies tenants) or delete.
 */
class PlatformReleaseController extends Controller
{
    public function __construct(private readonly ReleasePublisher $publisher) {}

    public function index(Request $request): View
    {
        $status = in_array($request->input('status'), array_keys(config('releases.statuses')), true)
            ? $request->input('status')
            : null;

        $releases = PlatformRelease::query()
            ->with(['creator', 'publisher'])
            ->withCount(['tenants'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("status = 'draft' desc")
            ->orderByDesc('released_on')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $notified = DB::connection('central')->table('notifications')
            ->whereIn('platform_release_id', $releases->pluck('id'))
            ->selectRaw('platform_release_id, COUNT(*) as total, SUM(read_at IS NOT NULL) as seen')
            ->groupBy('platform_release_id')
            ->get()
            ->keyBy('platform_release_id');

        return view('central.releases.index', [
            'activeNav' => 'releases',
            'releases' => $releases,
            'notified' => $notified,
            'selectedStatus' => $status,
            'canPublish' => $this->admin()->isSuperAdmin(),
        ]);
    }

    public function create(): View
    {
        return view('central.releases.form', $this->formData(new PlatformRelease([
            'released_on' => now(),
            'audience' => 'all',
            'category' => 'new_feature',
        ])));
    }

    public function store(PlatformReleaseRequest $request): RedirectResponse
    {
        $release = DB::connection('central')->transaction(function () use ($request) {
            $release = PlatformRelease::create([
                ...$request->safe()->except('tenant_ids'),
                'status' => 'draft',
                'created_by' => $this->admin()->getKey(),
            ]);
            $release->tenants()->sync($request->validated('tenant_ids', []));

            return $release;
        });

        if ($request->input('action') === 'publish') {
            return $this->publish($release);
        }

        return redirect()->route('central.releases.index')->with('success', __('Draft release saved. Tenants are not notified until it is published.'));
    }

    public function edit(PlatformRelease $release): View
    {
        return view('central.releases.form', $this->formData($release->load('tenants')));
    }

    public function update(PlatformReleaseRequest $request, PlatformRelease $release): RedirectResponse
    {
        DB::connection('central')->transaction(function () use ($request, $release) {
            $release->update($request->safe()->except('tenant_ids'));
            $release->tenants()->sync($request->validated('tenant_ids', []));
        });

        if ($request->input('action') === 'publish' && ! $release->isPublished()) {
            return $this->publish($release);
        }

        $message = __('Release updated.');

        if ($release->isPublished()) {
            // Reach any newly targeted tenants; existing recipients are never notified twice.
            $new = $this->publisher->notifyAudience($release->fresh());
            if ($new > 0) {
                $message .= ' '.trans_choice('{1} Notified 1 new user.|[2,*] Notified :count new users.', $new, ['count' => $new]);
            }
        }

        return redirect()->route('central.releases.index')->with('success', $message);
    }

    public function publish(PlatformRelease $release): RedirectResponse
    {
        abort_unless($this->admin()->isSuperAdmin(), 403, __('Only super admins can publish releases.'));

        try {
            $count = $this->publisher->publish($release, $this->admin());
        } catch (InvalidArgumentException $e) {
            return redirect()->route('central.releases.edit', $release)->withErrors(['tenant_ids' => $e->getMessage()]);
        }

        return redirect()->route('central.releases.index')->with(
            'success',
            __('":title" published.', ['title' => $release->title]).' '
                .trans_choice('{0} No new users to notify.|{1} 1 user notified.|[2,*] :count users notified.', $count, ['count' => $count]),
        );
    }

    public function destroy(PlatformRelease $release): RedirectResponse
    {
        abort_unless($this->admin()->isSuperAdmin(), 403, __('Only super admins can delete releases.'));

        DB::connection('central')->transaction(function () use ($release) {
            $this->publisher->retract($release);
            $release->delete();
        });

        return redirect()->route('central.releases.index')->with('success', __('Release deleted and its notifications removed.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(PlatformRelease $release): array
    {
        return [
            'activeNav' => 'releases',
            'release' => $release,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'selectedTenants' => old('tenant_ids', $release->exists ? $release->tenants->pluck('id')->all() : []),
            'modules' => collect(config('workspace_access.modules', []))->pluck('label', 'key')->all(),
            'canPublish' => $this->admin()->isSuperAdmin(),
        ];
    }

    private function admin(): AdminUser
    {
        $admin = auth('admin')->user();
        abort_unless($admin instanceof AdminUser, 403);

        return $admin;
    }
}
