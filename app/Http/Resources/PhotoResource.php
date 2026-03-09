<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource
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
            'description' => $this->description,
            'year_from' => $this->year_from,
            'year_to' => $this->year_to,
            'date_precision' => $this->date_precision,
            'source_credit' => $this->source_credit,
            'heading' => $this->heading,
            'pitch' => $this->pitch,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'place' => new PlaceResource($this->whenLoaded('place')),
            'files' => PhotoFileResource::collection($this->whenLoaded('files')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'persons' => PersonResource::collection($this->whenLoaded('persons')),
            'upvotes_count' => $this->upvotes_count,
            'downvotes_count' => $this->downvotes_count,
            'score' => $this->score(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
