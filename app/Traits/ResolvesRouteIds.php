<?php

namespace App\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

trait ResolvesRouteIds
{
    protected function resolveRouteId(mixed $value, string $parameter = 'id'): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        throw new HttpResponseException(
            $this->error(
                [$parameter => ["The {$parameter} must be a valid numeric identifier."]],
                'Validation failed.',
                422
            )
        );
    }
}
