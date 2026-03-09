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

        $this->calculatePhash($contents);
    }

    /**
     * Calculate and store a perceptual hash for the photo.
     *
     * Resizes the image to 8x8, converts to grayscale, and generates a
     * 64-bit hash where each bit indicates whether a pixel is above or
     * below the average luminance.
     */
    private function calculatePhash(string $contents): void
    {
        try {
            $source = @imagecreatefromstring($contents);

            if ($source === false) {
                Log::warning('ProcessPhotoUpload: could not create image for pHash', [
                    'photo_id' => $this->photo->id,
                ]);

                return;
            }

            $resized = imagescale($source, 8, 8);
            imagedestroy($source);

            if ($resized === false) {
                Log::warning('ProcessPhotoUpload: imagescale failed for pHash', [
                    'photo_id' => $this->photo->id,
                ]);

                return;
            }

            $pixels = [];

            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $rgb = imagecolorat($resized, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $pixels[] = (int) round(0.299 * $r + 0.587 * $g + 0.114 * $b);
                }
            }

            imagedestroy($resized);

            $average = array_sum($pixels) / count($pixels);

            $bits = '';
            foreach ($pixels as $pixel) {
                $bits .= $pixel >= $average ? '1' : '0';
            }

            $hex = '';
            for ($i = 0; $i < 64; $i += 4) {
                $hex .= dechex(bindec(substr($bits, $i, 4)));
            }

            $this->photo->update(['phash' => $hex]);
        } catch (\Throwable $e) {
            Log::error('ProcessPhotoUpload: pHash calculation failed', [
                'photo_id' => $this->photo->id,
                'error' => $e->getMessage(),
            ]);
        }
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
