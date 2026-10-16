<?php

namespace App\Services\Marketplace;

use App\Models\Central\MarketplaceListing;
use App\Models\Central\MarketplaceListingStatusChange;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarketplaceListingStatusService
{
    public function change(MarketplaceListing $listing, string $status, ?string $note = null): MarketplaceListing
    {
        return DB::connection('central')->transaction(function () use ($listing, $status, $note): MarketplaceListing {
            $listing = MarketplaceListing::query()->lockForUpdate()->findOrFail($listing->id);
            $previousStatus = $listing->status;

            if ($previousStatus === $status) {
                return $listing;
            }

            if ($status === 'active' && $previousStatus !== 'approved') {
                throw ValidationException::withMessages([
                    'status' => __('Approve the listing before publishing it.'),
                ]);
            }

            $listing->update([
                'status' => $status,
                'is_verified' => in_array($status, ['approved', 'active'], true),
            ]);

            MarketplaceListingStatusChange::query()->create([
                'listing_id' => $listing->id,
                'admin_user_id' => auth('admin')->id(),
                'user_id' => auth('admin')->check() ? null : auth()->id(),
                'from_status' => $previousStatus,
                'to_status' => $status,
                'note' => $note,
            ]);

            return $listing->fresh();
        });
    }
}
