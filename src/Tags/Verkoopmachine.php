<?php

namespace Tinusg\VerkoopmachineStatamic\Tags;

use Illuminate\Support\Str;
use Statamic\Facades\Addon;
use Statamic\Tags\Tags;
use Tinusg\VerkoopmachineStatamic\Http\VerkoopmachineApi;

class Verkoopmachine extends Tags
{
    public function __construct(private readonly VerkoopmachineApi $api) {}

    public function styles(): string
    {
        return '<link rel="stylesheet" href="'.e(asset('vendor/verkoopmachine-statamic/css/verkoopmachine.css')).'">';
    }

    /**
     * @return array<string, mixed>
     */
    public function content(): array
    {
        $slug = Addon::get('tinusg/verkoopmachine-statamic')->settings()->get('client_slug');

        if (! is_string($slug) || blank($slug)) {
            return $this->unavailable('Er is nog geen Verkoopmachine-client ingesteld.');
        }

        try {
            return [
                'available' => true,
                'error' => null,
                ...$this->api->content($slug, $this->types(), $this->limit(), 'nl'),
            ];
        } catch (\Throwable) {
            return $this->unavailable('De Verkoopmachine-inhoud is momenteel niet beschikbaar.');
        }
    }

    public function overview(): string
    {
        return view('verkoopmachine-statamic::overview', $this->content())->render();
    }

    /**
     * @return array<int, string>
     */
    private function types(): array
    {
        return Str::of((string) $this->params->get('types', 'vee|rundveeveilingen'))
            ->explode('|')
            ->map(fn (string $type): string => trim($type))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function limit(): int
    {
        return max(1, min(24, (int) $this->params->get('limit', 6)));
    }

    /**
     * @return array{available: false, error: string, client: null, groups: array<int, array<mixed>>}
     */
    private function unavailable(string $error): array
    {
        return [
            'available' => false,
            'error' => $error,
            'client' => null,
            'groups' => [],
        ];
    }
}
