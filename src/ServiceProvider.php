<?php

namespace Tinusg\VerkoopmachineStatamic;

use Tinusg\VerkoopmachineStatamic\Http\VerkoopmachineApi;
use Tinusg\VerkoopmachineStatamic\Rules\ClientSlugExists;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishables = [
        __DIR__.'/../resources/css' => 'css',
    ];

    public function bootAddon(): void
    {
        $api = $this->app->make(VerkoopmachineApi::class);

        $this->registerSettingsBlueprint([
            'tabs' => [
                'main' => [
                    'display' => 'Verkoopmachine',
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'client_code',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => 'Koppelcode',
                                        'instructions' => 'De Koppelcode van de Verkoopmachine-client waarvan je content wilt tonen.',
                                        'validate' => [
                                            'required',
                                            'string',
                                            'alpha_dash',
                                            new ClientSlugExists($api),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
