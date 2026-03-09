<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
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
            'created_at' => $this->created_at,
            'total_points' => $this->total_points,
            'level' => new LevelResource($this->currentLevel()),
            'badges' => BadgeResource::collection($this->whenLoaded('badges')),
            'photos_count' => $this->photos_count,
        ];
    }
}
