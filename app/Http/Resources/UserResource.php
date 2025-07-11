<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'city' => $this->city,
            'email_verified_at' => $this->email_verified_at?->setTimezone('Europe/Moscow'),
            'email_verified' => $this->hasVerifiedEmail(),
            'created_at' => $this->created_at->setTimezone('Europe/Moscow'),
            'updated_at' => $this->updated_at->setTimezone('Europe/Moscow'),
        ];
    }
}
