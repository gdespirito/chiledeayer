<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaceResource extends JsonResource
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
            'slug' => $this->slug,
            'type' => $this->type,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'google_place_id' => $this->google_place_id,
            'bounding_box' => $this->bounding_box,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'photos_count' => $this->whenCounted('photos'),
            'preview_photos' => $this->when(
                $this->relationLoaded('photos'),
                fn () => $this->photos->map(fn ($photo) => [
                    'id' => $photo->id,
                    'url' => $photo->files->first(fn ($f) => $f->variant === 'thumb')?->url()
                        ?? $photo->files->first(fn ($f) => $f->variant === 'medium')?->url()
                        ?? $photo->files->first()?->url(),
                ]),
            ),
        ];
    }
}
