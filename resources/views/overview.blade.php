@php
    $dateParts = static function (mixed $date): ?array {
        if (blank($date)) {
            return null;
        }

        try {
            $date = \Illuminate\Support\Carbon::parse((string) $date)->locale('nl');

            return [
                'datetime' => $date->toDateString(),
                'day' => $date->translatedFormat('j'),
                'month' => $date->translatedFormat('M'),
            ];
        } catch (\Throwable) {
            return null;
        }
    };

    $clientName = data_get($client ?? null, 'name');
@endphp

<section data-verkoopmachine>
    @if (! $available)
        <p class="vm-message vm-message--error" role="status">{{ $error }}</p>
    @elseif (collect($groups)->every(fn (array $group): bool => $group['items'] === []))
        <p class="vm-message">Er is momenteel geen actueel aanbod.</p>
    @else
        @foreach ($groups as $group)
            @if ($group['items'] !== [])
                @php
                    $total = (int) $group['total'];
                    $resultLabel = match ($group['key']) {
                        'vee' => $total === 1 ? 'advertentie' : 'advertenties',
                        'rundveeveilingen' => $total === 1 ? 'veiling' : 'veilingen',
                        default => $total === 1 ? 'resultaat' : 'resultaten',
                    };
                @endphp

                <section class="vm-group vm-group--{{ $group['key'] }}" aria-labelledby="vm-group-{{ $group['key'] }}">
                    <div class="vm-group__heading">
                        <h2 id="vm-group-{{ $group['key'] }}">{{ $group['label'] }}</h2>
                        <span>{{ $total }} {{ $resultLabel }}</span>
                    </div>

                    <div class="vm-grid vm-grid--{{ $group['key'] }}">
                        @foreach ($group['items'] as $item)
                            @php
                                $display = $item['display'] ?? [];
                            @endphp

                            @if ($group['key'] === 'vee')
                                @php
                                    $transaction = $display['transaction'] ?? null;
                                    $isWanted = is_string($transaction)
                                        && mb_strtolower($transaction) === 'gevraagd';
                                    $quantity = isset($display['quantity']) && is_numeric($display['quantity'])
                                        ? (int) $display['quantity']
                                        : null;
                                    $placeholderImageUrl = $item['placeholder_image_url'] ?? $item['image_url'] ?? null;
                                    $placeholderOverlayUrl = $item['placeholder_overlay_url'] ?? null;
                                    $photoCount = isset($item['photo_count']) && is_numeric($item['photo_count'])
                                        ? max(0, (int) $item['photo_count'])
                                        : 0;
                                    $categoryAndBreed = collect([
                                        $display['category'] ?? null,
                                        $display['breed'] ?? null,
                                    ])->filter()->unique()->join(' · ');
                                @endphp

                                <a class="vm-card vm-card--vee" href="{{ $item['url'] }}">
                                    <div class="vm-card__media">
                                        @if ($placeholderImageUrl)
                                            <img
                                                class="vm-card__image vm-card__image--placeholder{{ $placeholderOverlayUrl ? ' vm-card__image--blurred' : '' }}"
                                                src="{{ $placeholderImageUrl }}"
                                                alt=""
                                                loading="lazy"
                                            >
                                        @endif

                                        @if ($placeholderOverlayUrl)
                                            <img class="vm-card__overlay" src="{{ $placeholderOverlayUrl }}" alt="" loading="lazy">
                                        @endif

                                        @if ($isWanted)
                                            <span class="vm-card__badge vm-card__badge--wanted">Gevraagd</span>
                                        @endif

                                        @if ($quantity)
                                            <span class="vm-card__badge vm-card__badge--quantity">
                                                {{ number_format($quantity, 0, ',', '.') }} stuks
                                            </span>
                                        @endif

                                        @if ($photoCount > 0)
                                            <span class="vm-card__badge vm-card__badge--photos" aria-label="{{ $photoCount }} {{ $photoCount === 1 ? 'foto' : 'foto\'s' }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                                </svg>
                                                <span aria-hidden="true">{{ $photoCount }}</span>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="vm-card__body">
                                        @if ($clientName)
                                            <span class="vm-card__seller">{{ $clientName }}</span>
                                        @endif

                                        <h3>{{ $item['title'] }}</h3>

                                        @if ($categoryAndBreed)
                                            <p class="vm-card__meta">{{ $categoryAndBreed }}</p>
                                        @endif

                                        @if (filled($display['price']['label'] ?? null))
                                            <p class="vm-card__price">{{ $display['price']['label'] }}</p>
                                        @endif
                                    </div>
                                </a>
                            @elseif ($group['key'] === 'rundveeveilingen')
                                @php
                                    $dates = collect([
                                        $dateParts($display['starts_on'] ?? null),
                                        $dateParts($display['ends_on'] ?? null),
                                    ])->filter()->unique('datetime')->values();
                                    $place = collect([
                                        $display['location'] ?? null,
                                        $display['city'] ?? null,
                                    ])->filter()->unique()->join(', ');
                                    $country = $display['country'] ?? null;
                                @endphp

                                <a class="vm-card vm-card--auction" href="{{ $item['url'] }}">
                                    @if ($dates->isNotEmpty())
                                        <div class="vm-auction__dates" aria-label="Veilingdatum">
                                            @foreach ($dates as $date)
                                                <time class="vm-auction__date" datetime="{{ $date['datetime'] }}">
                                                    <span>{{ $date['day'] }}</span>
                                                    <small>{{ $date['month'] }}</small>
                                                </time>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="vm-auction__content">
                                        <h3>{{ $item['title'] }}</h3>

                                        @if ($place || $country)
                                            <div class="vm-auction__location">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                </svg>
                                                <span>
                                                    {{ $place }}@if ($place && $country) <span aria-hidden="true">·</span> @endif{{ $country }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @else
                                <a class="vm-card vm-card--default" href="{{ $item['url'] }}">
                                    @if ($item['image_url'])
                                        <img class="vm-card__image" src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}" loading="lazy">
                                    @endif
                                    <div class="vm-card__body">
                                        <h3>{{ $item['title'] }}</h3>
                                    </div>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    @endif
</section>
