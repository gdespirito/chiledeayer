<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    /** @use HasFactory<\Database\Factories\ReportFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'photo_id',
        'reason',
        'duplicate_of_id',
        'status',
    ];

    /**
     * Get the user who submitted this report.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the photo being reported.
     *
     * @return BelongsTo<Photo, $this>
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    /**
     * Get the photo this one is a duplicate of.
     *
     * @return BelongsTo<Photo, $this>
     */
    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'duplicate_of_id');
    }
}
