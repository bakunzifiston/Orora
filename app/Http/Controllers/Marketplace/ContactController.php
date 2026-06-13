<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\ContactMessageRequest;
use App\Models\Central\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('marketplace.contact.index', [
            'activePage' => 'contact',
            'contact' => config('marketplace.contact'),
            'social' => config('marketplace.social'),
            'success' => session('contact_success'),
        ]);
    }

    public function store(ContactMessageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        ContactMessage::query()->create([
            ...$data,
            'status' => 'new',
        ]);

        return redirect()
            ->route('marketplace.contact')
            ->with('contact_success', [
                'name' => $data['name'],
                'email' => $data['email'],
            ]);
    }
}
