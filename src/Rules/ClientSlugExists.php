<?php

namespace Tinusg\VerkoopmachineStatamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Tinusg\VerkoopmachineStatamic\Http\VerkoopmachineApi;

class ClientSlugExists implements ValidationRule
{
    public function __construct(private readonly VerkoopmachineApi $api) {}

    /**
     * @param  Closure(string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            if (! $this->api->clientExists((string) $value)) {
                $fail('Deze Koppelcode bestaat niet in Verkoopmachine.');
            }
        } catch (\Throwable) {
            $fail('De Koppelcode kon momenteel niet worden gecontroleerd.');
        }
    }
}
