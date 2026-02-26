<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        Carbon::setLocale('id'); // Agar "2 minutes ago" jadi "2 menit yang lalu"

        return [
            'id'         => $this->id,
            'sales_id'   => $this->sales_id,
            'title'      => $this->title,
            'message'    => $this->message,
            'is_read'    => (bool) $this->is_read,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'time_ago'   => $this->created_at?->diffForHumans() ?? null,
        ];
    }
}