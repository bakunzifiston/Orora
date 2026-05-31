@if (session('success'))
    <div class="dash-alert dash-alert--success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="dash-alert dash-alert--error">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="dash-alert dash-alert--error">
        <ul style="margin: 0; padding-left: 1.15rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
