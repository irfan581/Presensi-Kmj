<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'message'    => $this->message,
            'is_read'    => (bool) $this->is_read, // Pastikan boolean
            'created_at' => $this->created_at?->toIso8601String(), // Format standar mobile
        ];
    }
}