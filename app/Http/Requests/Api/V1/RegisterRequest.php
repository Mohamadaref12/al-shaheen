<?php

namespace App\Http\Requests\Api\V1;

use App\Rules\ValidImagePath;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'type'           => ['required', 'in:reader,contributor,writer'],
            'name'           => ['required', 'string', 'min:2', 'max:255'],
            'email'          => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password'       => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'locale'         => ['nullable', 'in:ar,en'],
            'country'        => ['nullable', 'string', 'max:100'],
            'language'       => ['nullable', 'string', 'max:10'],
            // contributor / writer
            'bio'            => ['nullable', 'string', 'max:2000'],
            'portfolio_link' => ['nullable', 'url', 'max:500'],
            // writer only
            'display_name'   => ['nullable', 'string', 'max:255'],
        ];

        if (in_array($this->input('type'), ['contributor', 'writer'], true)) {
            $rules = array_merge($rules, [
                'profile_photo' => ['required', 'string', 'max:500', new ValidImagePath('uploads/profiles')],
                'categories'    => ['required', 'array', 'min:1'],
                'categories.*'  => ['integer', 'exists:categories,id'],
            ]);
        }

        if ($this->input('type') === 'writer') {
            $rules = array_merge($rules, [
                'experience_level'        => ['required', 'in:junior,mid,senior,expert'],
                'languages'               => ['required', 'array', 'min:1'],
                'languages.*'             => ['string', 'max:50'],
                'editorial_specialties'   => ['required', 'array', 'min:1'],
                'editorial_specialties.*' => ['string', 'max:100'],
            ]);
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if (! in_array($this->input('type'), ['contributor', 'writer'], true)) {
            return;
        }

        $fields = ['categories'];

        if ($this->input('type') === 'writer') {
            $fields = array_merge($fields, ['languages', 'editorial_specialties']);
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
            'type.required'      => 'Account type is required.',
            'type.in'            => 'Invalid account type. Allowed values: reader, contributor, writer.',
            'name.required'      => 'Name is required.',
            'name.min'           => 'Name must be at least 2 characters.',
            'email.required'     => 'Email address is required.',
            'email.email'        => 'Invalid email address format.',
            'email.unique'       => 'This email address is already registered.',
            'password.required'  => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'locale.in'          => 'Locale must be ar or en.',
            'portfolio_link.url' => 'Portfolio link must be a valid URL.',
            'profile_photo.required'         => 'Profile photo path is required. Upload the image first.',
            'categories.required'            => 'At least one writing category is required.',
            'categories.min'                 => 'At least one writing category is required.',
            'categories.*.exists'            => 'One or more selected categories are invalid.',
            'experience_level.required'      => 'Experience level is required.',
            'experience_level.in'            => 'Experience level must be junior, mid, senior, or expert.',
            'languages.required'             => 'At least one language is required.',
            'languages.min'                  => 'At least one language is required.',
            'editorial_specialties.required' => 'At least one editorial specialty is required.',
            'editorial_specialties.min'      => 'At least one editorial specialty is required.',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Validation failed.',
                'data'    => $validator->errors(),
            ], 422)
        );
    }
}
