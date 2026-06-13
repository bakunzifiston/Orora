<section class="ab-section ab-section--grey ab-team">
    <div class="mp-container">
        <div class="ab-section__header ab-section__header--center">
            <h2 class="ab-section__title">Meet the Team</h2>
            <p class="ab-section__subtitle">The people building Orora Farm</p>
        </div>
        <div class="ab-team__grid">
            @foreach ($about['team'] as $member)
                <article class="ab-team-card">
                    @if (! empty($member['photo']))
                        <img src="{{ asset($member['photo']) }}" alt="{{ $member['name'] }}" class="ab-team-card__photo">
                    @else
                        <div class="ab-team-card__avatar" aria-hidden="true">{{ $member['initials'] }}</div>
                    @endif
                    <h3 class="ab-team-card__name">{{ $member['name'] }}</h3>
                    <p class="ab-team-card__role">{{ $member['role'] }}</p>
                    @if (! empty($member['linkedin']))
                        <a
                            href="{{ $member['linkedin'] }}"
                            class="ab-team-card__linkedin"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="{{ $member['name'] }} on LinkedIn"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.062 2.062 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
