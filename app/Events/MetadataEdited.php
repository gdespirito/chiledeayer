<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MetadataEdited
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  array<string, mixed>  $changes
     */
    public function __construct(
        public int $photoId,
        public int $userId,
        public array $changes,
    ) {}
}
