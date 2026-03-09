<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;

class DuplicateCheckController extends Controller
{
    /**
     * Check a file for potential duplicate photos by perceptual hash.
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', File::image()->max(20 * 1024)],
        ]);

        $file = $request->file('file');
        $contents = file_get_contents($file->getRealPath());
        $phash = $this->calculatePhash($contents);

        if ($phash === null) {
            return response()->json(['duplicates' => []]);
        }

        $candidates = Photo::query()
            ->whereNotNull('phash')
            ->with(['files' => fn ($q) => $q->where('variant', 'thumb')])
            ->latest()
            ->limit(500)
            ->get();

        $duplicates = $candidates
            ->filter(fn (Photo $photo) => $this->hammingDistance($phash, $photo->phash) < 10)
            ->map(fn (Photo $photo) => [
                'id' => $photo->id,
                'title' => $photo->title,
                'thumbnail' => $photo->files->first()?->url(),
                'distance' => $this->hammingDistance($phash, $photo->phash),
            ])
            ->values();

        return response()->json(['duplicates' => $duplicates]);
    }

    /**
     * Calculate perceptual hash from image contents using GD.
     */
    private function calculatePhash(string $contents): ?string
    {
        $source = @imagecreatefromstring($contents);

        if ($source === false) {
            return null;
        }

        $resized = imagescale($source, 8, 8);
        imagedestroy($source);

        if ($resized === false) {
            return null;
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

        return $hex;
    }

    /**
     * Calculate Hamming distance between two hex-encoded perceptual hashes.
     */
    private function hammingDistance(string $hash1, string $hash2): int
    {
        if (strlen($hash1) !== strlen($hash2)) {
            return PHP_INT_MAX;
        }

        $distance = 0;

        for ($i = 0; $i < strlen($hash1); $i++) {
            $xor = hexdec($hash1[$i]) ^ hexdec($hash2[$i]);
            $distance += substr_count(decbin($xor), '1');
        }

        return $distance;
    }
}
