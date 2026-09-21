<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\ContactMessage;
use App\Services\Marketplace\MarketplaceDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(): View
    {
        if (! MarketplaceDatabase::contactReady()) {
            return view('central.contact-messages.index', [
                'activeNav' => 'contact',
                'messages' => MarketplaceDatabase::emptyPaginator(),
                'contactReady' => false,
                'stats' => $this->emptyStats(),
            ]);
        }

        $messages = ContactMessage::query()
            ->orderByDesc('created_at')
            ->paginate(20);

        $base = ContactMessage::query();

        return view('central.contact-messages.index', [
            'activeNav' => 'contact',
            'messages' => $messages,
            'contactReady' => true,
            'stats' => [
                'total' => (clone $base)->count(),
                'new' => (clone $base)->where('status', 'new')->count(),
                'read' => (clone $base)->where('status', 'read')->count(),
                'replied' => (clone $base)->where('status', 'replied')->count(),
            ],
        ]);
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,read,replied,archived'],
        ]);

        $contactMessage->update([
            'status' => $validated['status'],
            'replied_at' => $validated['status'] === 'replied' ? now() : $contactMessage->replied_at,
        ]);

        return back()->with('success', 'Message updated.');
    }

    /**
     * @return array{total: int, new: int, read: int, replied: int}
     */
    private function emptyStats(): array
    {
        return [
            'total' => 0,
            'new' => 0,
            'read' => 0,
            'replied' => 0,
        ];
    }
}
