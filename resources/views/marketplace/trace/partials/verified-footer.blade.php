<section class="tr-verified">
    <div class="tr-verified__inner">
        <p class="tr-verified__badge">✅ This record is verified by Orora Farm</p>
        <p class="tr-verified__meta">
            Generated: {{ $generatedAt->format('F j, Y \a\t g:i A') }}<br>
            Verification Token: <code>{{ $verificationToken }}</code>
        </p>
        <a href="{{ route('marketplace.trace.pdf', $animal) }}" class="lp-btn lp-btn--gold lp-btn--lg">
            📥 Download Full PDF Report
        </a>
    </div>
</section>
