@extends('layouts.admin')

@section('title', 'Contact inbox')

@section('content')
    @include('modules.partials.header', [
        'title' => 'Contact inbox',
        'subtitle' => 'Messages submitted from the public contact form.',
    ])

    <div class="dash-panel">
        @if (empty($contactReady))
            <p class="dash-empty">Contact messages table is not set up yet. Run <code>php artisan migrate --force</code>.</p>
        @elseif ($messages->isEmpty())
            <p class="dash-empty">No contact messages yet.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>From</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($messages as $message)
                            <tr>
                                <td>{{ $message->created_at?->format('M j, Y g:i A') }}</td>
                                <td>
                                    <strong>{{ $message->name }}</strong>
                                    <div style="color: var(--orora-gray); font-size: 0.8125rem;">{{ $message->email }}</div>
                                </td>
                                <td>{{ $message->subject }}</td>
                                <td>{{ $message->inquiry_type ?? 'general' }}</td>
                                <td><span class="dash-badge-green">{{ ucfirst($message->status) }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('central.contact-messages.update', $message) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" style="margin: 0; width: auto; padding: 0.35rem 0.5rem; font-size: 0.8125rem; border: 1px solid #e5e7eb; border-radius: 0.375rem;">
                                            @foreach (['new', 'read', 'replied', 'archived'] as $status)
                                                <option value="{{ $status }}" @selected($message->status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6" style="color: var(--orora-gray); font-size: 0.8125rem; padding-top: 0;">{{ Str::limit($message->message, 180) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 1rem;">{{ $messages->links() }}</div>
        @endif
    </div>
@endsection
