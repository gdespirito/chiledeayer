<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Photo extends Model
{
    /** @use HasFactory<\Database\Factories\PhotoFactory> */
    use HasFactory, Searchable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'year_from',
        'year_to',
        'date_precision',
        'heading',
        'pitch',
        'place_id',
        'user_id',
        'source_credit',
        'phash',
        'upvotes_count',
        'downvotes_count',
        'visits_count',
        'featured_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year_from' => 'integer',
            'year_to' => 'integer',
            'heading' => 'float',
            'pitch' => 'float',
            'upvotes_count' => 'integer',
            'downvotes_count' => 'integer',
            'visits_count' => 'integer',
            'featured_at' => 'datetime',
        ];
    }

    /**
     * Get the user who uploaded this photo.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the place associated with this photo.
     *
     * @return BelongsTo<Place, $this>
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * Get the files for this photo.
     *
     * @return HasMany<PhotoFile, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(PhotoFile::class);
    }

    /**
     * Get the tags associated with this photo.
     *
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'photo_tag');
    }

    /**
     * Get the comments for this photo.
     *
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the votes for this photo.
     *
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the revisions for this photo.
     *
     * @return MorphMany<Revision, $this>
     */
    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }

    /**
     * Get the persons tagged in this photo.
     *
     * @return BelongsToMany<Person, $this>
     */
    public function persons(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'person_photo')
            ->withPivot('x', 'y', 'label')
            ->withTimestamps();
    }

    /**
     * Get the comparison photos ("foto del ahora") for this photo.
     *
     * @return HasMany<ComparisonPhoto, $this>
     */
    public function comparisons(): HasMany
    {
        return $this->hasMany(ComparisonPhoto::class);
    }

    /**
     * Get the visits for this photo.
     *
     * @return HasMany<PhotoVisit, $this>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(PhotoVisit::class);
    }

    /**
     * Get the reports for this photo.
     *
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Get the cached upvotes count.
     */
    public function upvotesCount(): int
    {
        return $this->upvotes_count;
    }

    /**
     * Get the cached downvotes count.
     */
    public function downvotesCount(): int
    {
        return $this->downvotes_count;
    }

    /**
     * Get the score (upvotes minus downvotes).
     */
    public function score(): int
    {
        return $this->upvotes_count - $this->downvotes_count;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'year_from' => $this->year_from,
            'year_to' => $this->year_to,
            'place_name' => $this->place?->name,
            'place_city' => $this->place?->city,
            'place_region' => $this->place?->region,
            'tags' => $this->tags->pluck('name')->toArray(),
            'persons' => $this->persons->pluck('name')->toArray(),
            'source_credit' => $this->source_credit,
            'score' => $this->score(),
            'created_at' => $this->created_at?->timestamp,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return ! $this->trashed();
    }
}
