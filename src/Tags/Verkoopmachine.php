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
        $path = 'vendor/verkoopmachine-statamic/css/verkoopmachine.css';
        $url = asset($path);
        $publishedPath = public_path($path);

        if (is_file($publishedPath)) {
            $url .= '?v='.filemtime($publishedPath);
        }

        return '<link rel="stylesheet" href="'.e($url).'">';
    }

    /**
     * @return array<string, mixed>
     */
    public function content(): array
    {
        $clientCode = Addon::get('tinusg/verkoopmachine-statamic')->settings()->get('client_code');

        if (! is_string($clientCode) || blank($clientCode)) {
            return $this->unavailable('Er is nog geen Verkoopmachine-client ingesteld.');
        }

        try {
            return [
                'available' => true,
                'error' => null,
                ...$this->api->content($clientCode, $this->types(), $this->limit(), 'nl'),
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
