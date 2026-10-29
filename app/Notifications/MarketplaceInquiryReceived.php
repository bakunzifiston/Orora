<?php

namespace App\Notifications;

use App\Models\Central\MarketplaceInquiry;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * In-app notice to a seller's workspace that a buyer asked about one of its shop listings.
 */
class MarketplaceInquiryReceived extends Notification
{
    public function __construct(public readonly MarketplaceInquiry $inquiry) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $listing = $this->inquiry->listing;

        return [
            'kind' => 'marketplace_inquiry',
            'inquiry_id' => $this->inquiry->id,
            'listing_id' => $listing?->id,
            'title' => __('New buyer inquiry: :title', ['title' => $listing?->title ?? __('your listing')]),
            'message' => Str::limit($this->inquiry->buyer_name.' · '.$this->inquiry->buyer_phone.' — '.trim(preg_replace('/\s+/', ' ', (string) $this->inquiry->message)), 140),
            'category' => 'marketplace',
            'category_label' => 'Marketplace',
            'url' => route('marketplace.shop.mine', [], false).'#inquiries',
        ];
    }
}
