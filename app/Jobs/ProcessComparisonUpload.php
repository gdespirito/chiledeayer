<?php

namespace App\Jobs;

use App\Models\ComparisonPhoto;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessComparisonUpload implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ComparisonPhoto $comparisonPhoto,
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
            Log::error('ProcessComparisonUpload: original file not found', [
                'comparison_photo_id' => $this->comparisonPhoto->id,
                'path' => $this->originalPath,
            ]);

            return;
        }

        $imageInfo = getimagesizefromstring($contents);

        if ($imageInfo === false) {
            Log::error('ProcessComparisonUpload: unable to read image dimensions', [
                'comparison_photo_id' => $this->comparisonPhoto->id,
                'path' => $this->originalPath,
            ]);

            return;
        }

        $this->comparisonPhoto->update([
            'original_path' => $this->originalPath,
        ]);

        $mimeType = $imageInfo['mime'];
        [$originalWidth, $originalHeight] = $imageInfo;
        $filename = basename($this->originalPath);
        $userId = $this->comparisonPhoto->user_id;

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
                Log::error("ProcessComparisonUpload: failed to create image resource for {$variant}", [
                    'comparison_photo_id' => $this->comparisonPhoto->id,
                ]);

                return;
            }

            if ($originalWidth <= $maxWidth) {
                $resized = $source;
            } else {
                $newHeight = (int) round($originalHeight * ($maxWidth / $originalWidth));
                $resized = imagescale($source, $maxWidth, $newHeight);

                if ($resized === false) {
                    Log::error("ProcessComparisonUpload: imagescale failed for {$variant}", [
                        'comparison_photo_id' => $this->comparisonPhoto->id,
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

            $variantPath = "comparisons/{$userId}/{$variant}/{$filename}";
            $disk = Storage::disk('s3');
            $disk->put($variantPath, $variantContents);

            $this->comparisonPhoto->update([
                "{$variant}_path" => $variantPath,
            ]);
        } catch (\Throwable $e) {
            Log::error("ProcessComparisonUpload: failed to generate {$variant} variant", [
                'comparison_photo_id' => $this->comparisonPhoto->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
