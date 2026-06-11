<?php

namespace App\Http\Requests\Api\V1;

use App\Models\User;
use App\Rules\ValidImagePath;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->user();
        $user?->loadMissing(['writer', 'contributor']);

        $userId = $user?->id;

        $rules = [
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', "unique:users,email,{$userId}"],
            'country'  => ['sometimes', 'nullable', 'string', 'max:100'],
            'language' => ['sometimes', 'nullable', 'string', 'max:10'],
            'locale'   => ['sometimes', 'in:ar,en'],
        ];

        if ($user?->contributor || $user?->writer) {
            $rules = array_merge($rules, [
                'bio'            => ['sometimes', 'nullable', 'string', 'max:2000'],
                'portfolio_link' => ['sometimes', 'nullable', 'url', 'max:500'],
                'profile_photo'  => ['sometimes', 'nullable', 'string', 'max:500', new ValidImagePath('uploads/profiles')],
                'categories'     => ['sometimes', 'array', 'min:1'],
                'categories.*'   => ['integer', 'exists:categories,id'],
            ]);
        }

        if ($user?->writer) {
            $rules = array_merge($rules, [
                'display_name'            => ['sometimes', 'nullable', 'string', 'max:255'],
                'experience_level'        => ['sometimes', 'nullable', 'in:junior,mid,senior,expert'],
                'languages'               => ['sometimes', 'nullable', 'array', 'min:1'],
                'languages.*'             => ['string', 'max:50'],
                'editorial_specialties'   => ['sometimes', 'nullable', 'array', 'min:1'],
                'editorial_specialties.*' => ['string', 'max:100'],
                'location'                => ['sometimes', 'nullable', 'string', 'max:255'],
                'social_links'            => ['sometimes', 'nullable', 'array'],
                'media_affiliation'       => ['sometimes', 'nullable', 'string', 'max:500'],
            ]);
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        /** @var User|null $user */
        $user = $this->user();
        $user?->loadMissing(['writer', 'contributor']);

        if (! $user?->contributor && ! $user?->writer) {
            return;
        }

        $fields = ['categories'];

        if ($user->writer) {
            $fields = array_merge($fields, ['languages', 'editorial_specialties', 'social_links']);
        }

        foreach ($fields as $field) {
            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge([$field => $decoded]);
            }
        }
    }

    public function messages(): array
    {
        return [
            'email.unique'                   => 'This email address is already registered.',
            'locale.in'                      => 'Locale must be ar or en.',
            'portfolio_link.url'             => 'Portfolio link must be a valid URL.',
            'profile_photo'                  => 'Profile photo path is invalid. Upload the image first.',
            'categories.min'                 => 'At least one category is required.',
            'categories.*.exists'            => 'One or more selected categories are invalid.',
            'experience_level.in'            => 'Experience level must be junior, mid, senior, or expert.',
            'languages.min'                  => 'At least one language is required.',
            'editorial_specialties.min'      => 'At least one editorial specialty is required.',
        ];
    }
}
