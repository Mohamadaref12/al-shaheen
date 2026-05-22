<?php

namespace App\Http\Requests\Api\V1;

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
        return [
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
