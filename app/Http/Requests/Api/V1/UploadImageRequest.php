<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'type'  => ['required', 'in:profile,featured,news,portfolio,general'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Image file is required.',
            'image.image'    => 'The uploaded file must be an image.',
            'image.mimes'    => 'Image must be a JPEG, PNG, or WebP file.',
            'image.max'      => 'Image must not exceed 5MB.',
            'type.required'  => 'Image type is required.',
            'type.in'        => 'Image type must be profile, featured, news, portfolio, or general.',
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
