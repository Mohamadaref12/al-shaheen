<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', "unique:users,email,{$userId}"],
            'country'  => ['sometimes', 'nullable', 'string', 'max:100'],
            'language' => ['sometimes', 'nullable', 'string', 'max:10'],
            'locale'   => ['sometimes', 'in:ar,en'],
        ];
    }
}
