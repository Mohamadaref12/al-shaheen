<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;

class ValidImagePath implements ValidationRule
{
    public function __construct(
        private readonly string $directoryPrefix = 'uploads/profiles',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('The :attribute must be a valid image path.');

            return;
        }

        if (str_contains($value, '..') || ! str_starts_with($value, $this->directoryPrefix . '/')) {
            $fail('The :attribute path is invalid.');

            return;
        }

        if (! Storage::disk('images')->exists($value)) {
            $fail('The :attribute was not found. Upload the image first.');
        }
    }
}
