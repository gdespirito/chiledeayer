<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    /** @use HasFactory<\Database\Factories\PlaceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'latitude',
        'longitude',
        'google_place_id',
        'bounding_box',
        'country',
        'region',
        'city',
        'verified',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bounding_box' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'verified' => 'boolean',
        ];
    }

    /**
     * Get the photos associated with this place.
     *
     * @return HasMany<Photo, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}
