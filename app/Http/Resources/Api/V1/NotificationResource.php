<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : [];

        $url = $data['url'] ?? null;

        if (! $url && isset($data['actions'][0]['url'])) {
            $url = $data['actions'][0]['url'];
        }

        return [
            'id'         => $this->id,
            'type'       => $data['type'] ?? class_basename((string) $this->type),
            'title'      => $data['title'] ?? null,
            'body'       => $data['body'] ?? null,
            'url'        => $url,
            'status'     => $data['status'] ?? null,
            'data'       => is_array($data['data'] ?? null) ? $data['data'] : [],
            'read_at'    => optional($this->read_at)?->toIso8601String(),
            'is_read'    => $this->read_at !== null,
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
