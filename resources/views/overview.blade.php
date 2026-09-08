<section data-verkoopmachine>
    @if (! $available)
        <p class="vm-message vm-message--error" role="status">{{ $error }}</p>
    @elseif (collect($groups)->every(fn (array $group): bool => $group['items'] === []))
        <p class="vm-message">Er is momenteel geen actuele Verkoopmachine-inhoud.</p>
    @else
        @foreach ($groups as $group)
            @if ($group['items'] !== [])
                <section class="vm-group" aria-labelledby="vm-group-{{ $group['key'] }}">
                    <div class="vm-group__heading">
                        <h2 id="vm-group-{{ $group['key'] }}">{{ $group['label'] }}</h2>
                        <span>{{ $group['total'] }} beschikbaar</span>
                    </div>

                    <div class="vm-grid vm-grid--{{ $group['key'] }}">
                        @foreach ($group['items'] as $item)
                            <a class="vm-card" href="{{ $item['url'] }}">
                                @if ($item['image_url'])
                                    <img class="vm-card__image" src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}" loading="lazy">
                                @endif

                                <div class="vm-card__body">
                                    <h3>{{ $item['title'] }}</h3>

                                    @if ($item['summary'])
                                        <p>{{ $item['summary'] }}</p>
                                    @endif

                                    <dl class="vm-card__facts">
                                        @foreach ($item['display'] as $key => $value)
                                            @continue(is_array($value) || is_bool($value) || blank($value))
                                            <div>
                                                <dt>{{ str($key)->replace('_', ' ')->headline() }}</dt>
                                                <dd>{{ $value }}</dd>
                                            </div>
                                        @endforeach

                                        @if (filled($item['display']['price']['label'] ?? null))
                                            <div>
                                                <dt>Prijs</dt>
                                                <dd>{{ $item['display']['price']['label'] }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    @endif
</section>
