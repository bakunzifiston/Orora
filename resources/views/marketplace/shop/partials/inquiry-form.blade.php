@props(['listing'])

<form method="POST" action="{{ route('marketplace.shop.inquiry', $listing) }}" class="shop-inquiry" id="inquiry">
    @csrf

    <h3 class="shop-inquiry__title">Send an inquiry</h3>

    <div class="shop-inquiry__grid">
        <div class="mp-field">
            <label for="buyer_name">Your Name *</label>
            <input type="text" id="buyer_name" name="buyer_name" value="{{ old('buyer_name') }}" required>
            @error('buyer_name')<p class="mp-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="mp-field">
            <label for="buyer_phone">Phone *</label>
            <input type="text" id="buyer_phone" name="buyer_phone" value="{{ old('buyer_phone') }}" required>
            @error('buyer_phone')<p class="mp-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="mp-field">
            <label for="buyer_email">Email</label>
            <input type="email" id="buyer_email" name="buyer_email" value="{{ old('buyer_email') }}">
            @error('buyer_email')<p class="mp-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="mp-field">
            <label for="buyer_location">Location</label>
            <input type="text" id="buyer_location" name="buyer_location" value="{{ old('buyer_location') }}">
            @error('buyer_location')<p class="mp-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="mp-field mp-field--full">
            <label for="message">Message *</label>
            <textarea id="message" name="message" rows="4" required placeholder="I’m interested…">{{ old('message') }}</textarea>
            @error('message')<p class="mp-field-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <button type="submit" class="lp-btn lp-btn--primary">Send</button>
</form>
