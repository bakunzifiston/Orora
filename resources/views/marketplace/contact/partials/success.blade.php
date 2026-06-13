<div class="ct-success">
    <div class="ct-success__icon" aria-hidden="true">✅</div>
    <h2 class="ct-success__title">Message Sent Successfully!</h2>
    <p class="ct-success__text">
        Thank you for reaching out, <strong>{{ $success['name'] }}</strong>.
        We have received your message and will get back to you within 24 hours at
        <strong>{{ $success['email'] }}</strong>.
    </p>
    <div class="ct-success__actions">
        <a href="{{ route('marketplace.contact') }}" class="ct-btn ct-btn--outline">Send Another Message</a>
        <a href="{{ route('marketplace.home') }}" class="ct-btn ct-btn--primary">Go to Home</a>
    </div>
</div>
