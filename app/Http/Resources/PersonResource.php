<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
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
            'type' => $this->type,
            'slug' => $this->slug,
            'bio' => $this->bio,
            'photos_count' => $this->whenCounted('photos'),
            'pivot' => $this->whenPivotLoaded('person_photo', fn () => [
                'x' => $this->pivot->x,
                'y' => $this->pivot->y,
                'label' => $this->pivot->label,
            ]),
        ];
    }
}
