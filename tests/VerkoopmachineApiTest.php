<?php

namespace Tinusg\VerkoopmachineStatamic\Tests;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tinusg\VerkoopmachineStatamic\Http\VerkoopmachineApi;

class VerkoopmachineApiTest extends TestCase
{
    public function test_it_checks_whether_a_client_exists(): void
    {
        config()->set('verkoopmachine-statamic.api_url', 'https://verkoopmachine.test/api/v1');

        Http::preventStrayRequests();
        Http::fake([
            'https://verkoopmachine.test/api/v1/clients/veehandel-jansen' => Http::response([
                'data' => ['slug' => 'veehandel-jansen', 'name' => 'Veehandel Jansen'],
            ]),
        ]);

        $this->assertTrue(app(VerkoopmachineApi::class)->clientExists('veehandel-jansen'));
    }

    public function test_it_returns_stale_content_when_the_api_is_temporarily_unavailable(): void
    {
        config()->set('verkoopmachine-statamic.api_url', 'https://verkoopmachine.test/api/v1');
        Cache::flush();

        Http::preventStrayRequests();
        Http::fake([
            'https://verkoopmachine.test/api/v1/clients/veehandel-jansen/content*' => Http::response([
                'data' => [
                    'client' => ['slug' => 'veehandel-jansen', 'name' => 'Veehandel Jansen'],
                    'groups' => [],
                ],
            ]),
        ]);

        $api = app(VerkoopmachineApi::class);
        $content = $api->content('veehandel-jansen', ['vee'], 6, 'nl');

        Http::fake([
            'https://verkoopmachine.test/api/v1/clients/veehandel-jansen/content*' => Http::failedConnection(),
        ]);
        Cache::forget('verkoopmachine-statamic:content:'.sha1(json_encode([
            'slug' => 'veehandel-jansen',
            'types' => ['vee'],
            'limit' => 6,
            'locale' => 'nl',
        ], JSON_THROW_ON_ERROR)));

        $this->assertSame($content, $api->content('veehandel-jansen', ['vee'], 6, 'nl'));
    }
}
