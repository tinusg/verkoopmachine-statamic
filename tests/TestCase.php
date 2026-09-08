<?php

namespace Tinusg\VerkoopmachineStatamic\Tests;

use Statamic\Testing\AddonTestCase;
use Tinusg\VerkoopmachineStatamic\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
