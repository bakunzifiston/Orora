<?php

namespace App\Services\Species;

use App\Models\Farm;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class ActiveFarmContext
{
    public const SESSION_KEY = 'active_farm_id';

    public function id(): ?int
    {
        $id = Session::get(self::SESSION_KEY);

        return is_numeric($id) ? (int) $id : null;
    }

    public function farm(): ?Farm
    {
        $id = $this->id();

        if (! $id) {
            return null;
        }

        return Farm::query()->find($id);
    }

    public function set(?int $farmId): void
    {
        if (! $farmId) {
            Session::forget(self::SESSION_KEY);

            return;
        }

        if (! Farm::query()->whereKey($farmId)->exists()) {
            Session::forget(self::SESSION_KEY);

            return;
        }

        Session::put(self::SESSION_KEY, $farmId);
    }

    /**
     * @return Collection<int, Farm>
     */
    public function tenantFarms(): Collection
    {
        return Farm::query()->orderBy('name')->get(['id', 'name', 'primary_species', 'status']);
    }

    public function rememberFromRequest(): void
    {
        $request = request();

        if ($request->exists('farm_id')) {
            $this->set($request->filled('farm_id') ? (int) $request->input('farm_id') : null);

            return;
        }

        $current = $this->farm();

        if ($current) {
            return;
        }

        $farms = $this->tenantFarms();

        if ($farms->count() === 1) {
            $this->set($farms->first()->id);
        }
    }
}
