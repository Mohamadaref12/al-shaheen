<?php

namespace App\Http\Resources\Api\V1\Profiles;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
