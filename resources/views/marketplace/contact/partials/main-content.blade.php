<section class="ct-main" id="contact-form">
    <div class="mp-container ct-main__grid">
        <div class="ct-form-area">
            @if ($success)
                @include('marketplace.contact.partials.success')
            @else
                @include('marketplace.contact.partials.form')
            @endif
        </div>

        <aside class="ct-info">
            <div class="ct-info__block">
                <h2 class="ct-info__heading">Office</h2>
                <p class="ct-info__text">{{ $contact['full_address'] }}</p>
            </div>

            <hr class="ct-info__divider">

            <div class="ct-info__block">
                <h2 class="ct-info__heading">Hours</h2>
                <ul class="ct-info__hours">
                    @foreach ($contact['hours'] as $slot)
                        <li class="ct-info__hours-item {{ $slot['open'] ? '' : 'ct-info__hours-item--closed' }}">
                            <span>
                                <strong>{{ $slot['label'] }}</strong><br>
                                {{ $slot['time'] }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <hr class="ct-info__divider">

            <div class="ct-info__block">
                <h2 class="ct-info__heading">Follow</h2>
                <div class="ct-info__social">
                    @foreach ($social as $link)
                        <a
                            href="{{ $link['url'] }}"
                            class="ct-info__social-link"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="{{ $link['label'] }}"
                        >{{ $link['label'] }}</a>
                    @endforeach
                </div>
            </div>

            <hr class="ct-info__divider">

            <div class="ct-info__block">
                <h2 class="ct-info__heading">Response time</h2>
                <ul class="ct-info__response">
                    @foreach ($contact['response_times'] as $item)
                        <li>
                            <strong>{{ $item['channel'] }}</strong> — {{ $item['time'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>
</section>
