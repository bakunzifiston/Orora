@props(['listing'])

<form method="POST" action="{{ route('marketplace.shop.inquiry', $listing) }}" class="shop-inquiry" id="inquiry">
    @csrf

    <div class="shop-inquiry__intro">
        <h2 class="shop-inquiry__title">Ask the seller</h2>
        <p class="shop-inquiry__text">Send a message about price, delivery or availability. The seller will reply by phone or email.</p>
    </div>

    <div class="shop-inquiry__form">
        <div class="shop-inquiry__grid">
            <div class="mp-field">
                <label for="buyer_name">Your name <span class="mp-required">*</span></label>
                <input type="text" id="buyer_name" autocomplete="name" name="buyer_name" value="{{ old('buyer_name') }}" required>
                @error('buyer_name')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="mp-field">
                <label for="buyer_phone">Phone <span class="mp-required">*</span></label>
                <input type="tel" id="buyer_phone" autocomplete="tel" name="buyer_phone" value="{{ old('buyer_phone') }}" required>
                @error('buyer_phone')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="mp-field">
                <label for="buyer_email">Email <span class="mp-optional">(optional)</span></label>
                <input type="email" id="buyer_email" name="buyer_email" value="{{ old('buyer_email') }}">
                @error('buyer_email')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="mp-field">
                <label for="buyer_location">Location <span class="mp-optional">(optional)</span></label>
                <input type="text" id="buyer_location" name="buyer_location" value="{{ old('buyer_location') }}">
                @error('buyer_location')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="mp-field mp-field--full">
                <label for="message">Message <span class="mp-required">*</span></label>
                <textarea id="message" name="message" rows="4" required placeholder="I’m interested…">{{ old('message') }}</textarea>
                @error('message')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="shop-inquiry__actions">
            <button type="submit" class="lp-btn lp-btn--primary">Send inquiry</button>
        </div>
    </div>
</form>
