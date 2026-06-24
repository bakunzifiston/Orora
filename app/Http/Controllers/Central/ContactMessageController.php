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
            ]);
        }

        $messages = ContactMessage::query()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('central.contact-messages.index', [
            'activeNav' => 'contact',
            'messages' => $messages,
            'contactReady' => true,
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
}
