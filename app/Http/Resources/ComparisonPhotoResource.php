<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ComparisonPhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $disk = Storage::disk('s3');

        return [
            'id' => $this->id,
            'photo_id' => $this->photo_id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'description' => $this->description,
            'taken_at' => $this->taken_at?->toDateString(),
            'original_url' => $this->original_path ? $disk->url($this->original_path) : null,
            'medium_url' => $this->medium_path ? $disk->url($this->medium_path) : null,
            'thumb_url' => $this->thumb_path ? $disk->url($this->thumb_path) : null,
            'created_at' => $this->created_at,
        ];
    }
}
