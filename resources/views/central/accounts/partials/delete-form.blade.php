<form method="POST" action="{{ route('central.accounts.destroy', $user) }}" class="{{ $formClass ?? '' }}" onsubmit="return confirm(@json($confirm ?? 'Delete this user and all related farm data? This cannot be undone.'));">
    @csrf
    @method('DELETE')
    <button type="submit" class="{{ $buttonClass ?? 'dash-data-table__delete' }}">{{ $label ?? 'Delete' }}</button>
</form>
