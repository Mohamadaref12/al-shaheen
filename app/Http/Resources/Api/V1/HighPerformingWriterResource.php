<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HighPerformingWriterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'display_name' => $this->display_name,
            'user'         => [
                'id'   => $this->user?->id,
                'name' => $this->user?->name,
            ],
        ];
    }
}
