<div class="ct-form-card">
    <h2 class="ct-form-card__title">Send Us a Message</h2>
    <p class="ct-form-card__subtitle">We reply within 24 hours</p>

    <form method="POST" action="{{ route('marketplace.contact.store') }}" class="ct-form" novalidate>
        @csrf

        <fieldset class="ct-fieldset">
            <legend class="ct-fieldset__legend">Inquiry Type <span class="ct-required">*</span></legend>
            <div class="ct-inquiry-types">
                @foreach ($contact['inquiry_types'] as $value => $label)
                    <label class="ct-inquiry-type">
                        <input
                            type="radio"
                            name="inquiry_type"
                            value="{{ $value }}"
                            {{ old('inquiry_type', 'general') === $value ? 'checked' : '' }}
                            required
                        >
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('inquiry_type')<p class="ct-field-error">{{ $message }}</p>@enderror
        </fieldset>

        <div class="ct-field">
            <label for="name">Full Name <span class="ct-required">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
            @error('name')<p class="ct-field-error">{{ $message }}</p>@enderror
        </div>

        <div class="ct-field">
            <label for="email">Email Address <span class="ct-required">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email')<p class="ct-field-error">{{ $message }}</p>@enderror
        </div>

        <div class="ct-field">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel">
            @error('phone')<p class="ct-field-error">{{ $message }}</p>@enderror
        </div>

        <div class="ct-field">
            <label for="subject">Subject <span class="ct-required">*</span></label>
            <input type="text" id="subject" name="subject" value="{{ old('subject', request('subject')) }}" required>
            @error('subject')<p class="ct-field-error">{{ $message }}</p>@enderror
        </div>

        <div class="ct-field">
            <label for="message">Message <span class="ct-required">*</span></label>
            <textarea id="message" name="message" rows="6" required>{{ old('message') }}</textarea>
            @error('message')<p class="ct-field-error">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="ct-btn ct-btn--submit">
            Send Message <span aria-hidden="true">→</span>
        </button>

        <p class="ct-form-card__privacy">
            <span aria-hidden="true">🔒</span>
            Your information is safe and never shared
        </p>
    </form>
</div>
