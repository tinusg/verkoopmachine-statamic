<?php

namespace Tinusg\VerkoopmachineStatamic\Tests;

use Statamic\Facades\Addon;

class SettingsTest extends TestCase
{
    public function test_it_uses_client_code_as_the_koppelcode_setting(): void
    {
        $fields = Addon::get('tinusg/verkoopmachine-statamic')
            ->settingsBlueprint()
            ->fields();

        $this->assertTrue($fields->has('client_code'));
        $this->assertFalse($fields->has('client_slug'));
    }
}
