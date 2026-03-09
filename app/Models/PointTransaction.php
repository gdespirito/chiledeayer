<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\PointTransactionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'point_action_id',
        'points',
        'actionable_type',
        'actionable_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    /**
     * Get the user who earned these points.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the point action for this transaction.
     *
     * @return BelongsTo<PointAction, $this>
     */
    public function pointAction(): BelongsTo
    {
        return $this->belongsTo(PointAction::class);
    }

    /**
     * Get the actionable model (photo, comment, vote, etc.).
     */
    public function actionable(): MorphTo
    {
        return $this->morphTo();
    }
}
