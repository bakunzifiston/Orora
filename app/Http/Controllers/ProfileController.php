<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $species = app(\App\Services\Species\SpeciesProfile::class);
        $access = app(\App\Services\WorkspaceAccessService::class);
        $groups = $access->filterNavigationGroups($species->navigationGroups(), auth()->user());

        return view('profile.edit', [
            'user' => auth()->user(),
            'navigation' => collect($groups)->flatMap(fn (array $g) => $g['items'] ?? [])->values()->all(),
            'navigationGroups' => $groups,
            'activeNav' => 'settings',
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->safe()->only(['name', 'email']));

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        return redirect()
            ->route('profile.edit')
            ->with('success', __('Profile updated successfully.'));
    }
}
