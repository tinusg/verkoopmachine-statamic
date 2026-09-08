<?php

namespace Tinusg\VerkoopmachineStatamic\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class VerkoopmachineApi
{
    public function clientExists(string $slug): bool
    {
        $response = $this->request()->get('clients/'.rawurlencode($slug));

        if ($response->notFound()) {
            return false;
        }

        $response->throw();

        return true;
    }

    /**
     * @param  array<int, string>  $types
     * @return array{client: array{slug: string, name: string}, groups: array<int, array<string, mixed>>}
     */
    public function content(string $slug, array $types, int $limit, string $locale): array
    {
        $cacheKey = $this->cacheKey($slug, $types, $limit, $locale);
        $staleCacheKey = "{$cacheKey}:stale";

        try {
            return Cache::remember($cacheKey, now()->addSeconds((int) config('verkoopmachine-statamic.cache_seconds')), function () use ($slug, $types, $limit, $locale, $staleCacheKey): array {
                $response = $this->request()->get('clients/'.rawurlencode($slug).'/content', [
                    'types' => implode(',', $types),
                    'limit' => $limit,
                    'locale' => $locale,
                ]);

                $response->throw();

                $content = Arr::get($response->json(), 'data');

                if (! is_array($content) || ! isset($content['client'], $content['groups'])) {
                    throw new RuntimeException('Verkoopmachine returned an invalid content response.');
                }

                Cache::put(
                    $staleCacheKey,
                    $content,
                    now()->addSeconds((int) config('verkoopmachine-statamic.stale_cache_seconds')),
                );

                return $content;
            });
        } catch (\Throwable $throwable) {
            $staleContent = Cache::get($staleCacheKey);

            if (is_array($staleContent)) {
                return $staleContent;
            }

            throw $throwable;
        }
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->baseUrl((string) config('verkoopmachine-statamic.api_url'))
            ->connectTimeout((int) config('verkoopmachine-statamic.connect_timeout'))
            ->timeout((int) config('verkoopmachine-statamic.timeout'))
            ->retry([100, 250], throw: false);
    }

    /**
     * @param  array<int, string>  $types
     */
    private function cacheKey(string $slug, array $types, int $limit, string $locale): string
    {
        return 'verkoopmachine-statamic:content:'.sha1(json_encode([
            'slug' => $slug,
            'types' => $types,
            'limit' => $limit,
            'locale' => $locale,
        ], JSON_THROW_ON_ERROR));
    }
}
