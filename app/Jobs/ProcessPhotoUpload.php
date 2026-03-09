<?php

namespace App\Jobs;

use App\Models\Photo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPhotoUpload implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Photo $photo,
        public string $originalPath,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $disk = Storage::disk('s3');
        $contents = $disk->get($this->originalPath);

        if ($contents === null) {
            Log::error('ProcessPhotoUpload: original file not found', [
                'photo_id' => $this->photo->id,
                'path' => $this->originalPath,
            ]);

            return;
        }

        $imageInfo = getimagesizefromstring($contents);

        if ($imageInfo === false) {
            Log::error('ProcessPhotoUpload: unable to read image dimensions', [
                'photo_id' => $this->photo->id,
                'path' => $this->originalPath,
            ]);

            return;
        }

        [$originalWidth, $originalHeight] = $imageInfo;

        $this->photo->files()->create([
            'variant' => 'original',
            'path' => $this->originalPath,
            'disk' => 's3',
            'width' => $originalWidth,
            'height' => $originalHeight,
            'size' => $disk->size($this->originalPath),
        ]);

        $mimeType = $imageInfo['mime'];
        $filename = basename($this->originalPath);
        $userId = $this->photo->user_id;

        $this->generateVariant($contents, $mimeType, $originalWidth, $originalHeight, 'medium', 1200, $userId, $filename);
        $this->generateVariant($contents, $mimeType, $originalWidth, $originalHeight, 'thumb', 400, $userId, $filename);
    }

    /**
     * Generate a resized variant and upload it to S3.
     */
    private function generateVariant(
        string $contents,
        string $mimeType,
        int $originalWidth,
        int $originalHeight,
        string $variant,
        int $maxWidth,
        int $userId,
        string $filename,
    ): void {
        try {
            $source = imagecreatefromstring($contents);

            if ($source === false) {
                Log::error("ProcessPhotoUpload: failed to create image resource for {$variant}", [
                    'photo_id' => $this->photo->id,
                ]);

                return;
            }

            if ($originalWidth <= $maxWidth) {
                $resized = $source;
                $newWidth = $originalWidth;
                $newHeight = $originalHeight;
            } else {
                $newWidth = $maxWidth;
                $newHeight = (int) round($originalHeight * ($maxWidth / $originalWidth));
                $resized = imagescale($source, $newWidth, $newHeight);

                if ($resized === false) {
                    Log::error("ProcessPhotoUpload: imagescale failed for {$variant}", [
                        'photo_id' => $this->photo->id,
                    ]);
                    imagedestroy($source);

                    return;
                }

                imagedestroy($source);
            }

            ob_start();

            if ($mimeType === 'image/png') {
                imagepng($resized, null, 9);
            } else {
                imagejpeg($resized, null, 85);
            }

            $variantContents = ob_get_clean();
            imagedestroy($resized);

            $variantPath = "photos/{$userId}/{$variant}/{$filename}";
            $disk = Storage::disk('s3');
            $disk->put($variantPath, $variantContents);

            $this->photo->files()->create([
                'variant' => $variant,
                'path' => $variantPath,
                'disk' => 's3',
                'width' => $newWidth,
                'height' => $newHeight,
                'size' => $disk->size($variantPath),
            ]);
        } catch (\Throwable $e) {
            Log::error("ProcessPhotoUpload: failed to generate {$variant} variant", [
                'photo_id' => $this->photo->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
