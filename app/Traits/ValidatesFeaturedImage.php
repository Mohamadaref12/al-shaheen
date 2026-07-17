<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ValidatesFeaturedImage
{
    /**
     * @return array<int, mixed>
     */
    protected function featuredImageRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:500',
            'not_regex:/\.\./',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (blank($value)) {
                    return;
                }

                if (! is_string($value) || ! str_starts_with($value, 'uploads/')) {
                    $fail('The featured image path must start with uploads/ (upload the image first).');

                    return;
                }

                if (! Storage::disk('images')->exists($value)) {
                    $fail('The featured image was not found. Upload it first via POST /uploads/images.');
                }
            },
        ];
    }

    protected function assertFeaturedImagePresent(?string $featuredImage): void
    {
        if (blank($featuredImage)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'featured_image' => ['The featured image field is required.'],
            ]);
        }
    }
}
