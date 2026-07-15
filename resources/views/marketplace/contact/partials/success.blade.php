<div class="ct-success">
    <div class="ct-success__mark" aria-hidden="true"></div>
    <h2 class="ct-success__title">Message sent</h2>
    <p class="ct-success__text">
        Thanks, <strong>{{ $success['name'] }}</strong>.
        We’ll reply within 24 hours at <strong>{{ $success['email'] }}</strong>.
    </p>
    <div class="ct-success__actions">
        <a href="{{ route('marketplace.contact') }}" class="ct-btn ct-btn--outline">Send another</a>
        <a href="{{ route('marketplace.home') }}" class="ct-btn ct-btn--primary">Home</a>
    </div>
</div>
