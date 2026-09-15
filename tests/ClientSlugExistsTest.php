<?php

namespace Tinusg\VerkoopmachineStatamic\Tests;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tinusg\VerkoopmachineStatamic\Http\VerkoopmachineApi;
use Tinusg\VerkoopmachineStatamic\Rules\ClientSlugExists;

class ClientSlugExistsTest extends TestCase
{
    public function test_it_rejects_an_unknown_client_code(): void
    {
        config()->set('verkoopmachine-statamic.api_url', 'https://verkoopmachine.test/api/v1');

        Http::preventStrayRequests();
        Http::fake([
            'https://verkoopmachine.test/api/v1/clients/onbekend-bedrijf' => Http::response([], 404),
        ]);

        $validator = Validator::make([
            'client_code' => 'onbekend-bedrijf',
        ], [
            'client_code' => [new ClientSlugExists(app(VerkoopmachineApi::class))],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertSame('Deze Koppelcode bestaat niet in Verkoopmachine.', $validator->errors()->first('client_code'));
    }

    public function test_it_reports_when_the_client_cannot_be_checked(): void
    {
        config()->set('verkoopmachine-statamic.api_url', 'https://verkoopmachine.test/api/v1');

        Http::preventStrayRequests();
        Http::fake([
            'https://verkoopmachine.test/api/v1/clients/veehandel-jansen' => Http::failedConnection(),
        ]);

        $validator = Validator::make([
            'client_code' => 'veehandel-jansen',
        ], [
            'client_code' => [new ClientSlugExists(app(VerkoopmachineApi::class))],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertSame('De Koppelcode kon momenteel niet worden gecontroleerd.', $validator->errors()->first('client_code'));
    }
}
