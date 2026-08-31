<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shrinks an uploaded photo to something worth storing and quick to look at.
 *
 * Payment receipts arrive as whatever the customer's phone produced — a modern
 * handset screenshot is routinely 4–8 MB and 3000px wide. None of that helps
 * the shop, who only needs to read an amount and a reference off it, and all of
 * it costs the vendor's dashboard a slow image load and the disk a large file.
 *
 * So the upload limit is generous and the STORED file is small: accept what the
 * phone gives, then re-encode once, server-side, before anything is written.
 *
 * Built on GD, which ships with this PHP install — deliberately no new
 * dependency for what is a resize and a re-encode.
 *
 * Three things happen, in this order, and the order matters:
 *
 *   1. EXIF orientation is applied. Phone cameras store the image sideways with
 *      a "rotate me" tag; GD ignores that tag, so skipping this step hands the
 *      shop a receipt lying on its side.
 *   2. The long edge is capped. This is where nearly all the saving comes from.
 *   3. Re-encode as JPEG, dropping the EXIF block entirely — which also strips
 *      the GPS coordinates phones bury in photos, so the shop never receives
 *      the customer's home location along with their receipt.
 */
class ImageCompressor
{
    /**
     * Longest edge of the stored image, in pixels.
     *
     * 1600 keeps a 12-digit reference comfortably legible when the vendor opens
     * the full-size view, while taking a 4000px phone screenshot down by an
     * order of magnitude in bytes.
     */
    private const MAX_EDGE = 1600;

    /**
     * JPEG quality steps, tried in order until the result fits TARGET_BYTES.
     *
     * 82 is where screenshot text stops softening and is what almost every
     * upload lands on. The lower steps exist for the awkward minority — busy
     * photographs of a screen, rather than a clean screenshot — which resize to
     * the same pixel count but hold far more detail. The floor is 60: below
     * that a 12-digit reference starts to break up, and an unreadable receipt
     * defeats the entire point of collecting one.
     */
    private const QUALITY_STEPS = [82, 70, 60];

    /**
     * What we aim to store. Not a hard cap — if even quality 60 cannot reach
     * it, the best attempt is kept rather than the image being degraded
     * further or the upload rejected.
     */
    private const TARGET_BYTES = 400 * 1024;

    /**
     * Above this, GD would need more memory than the job is worth. A 50MP image
     * decodes to ~200MB of raw bitmap regardless of its file size, so the guard
     * is on PIXELS, not bytes — a heavily-compressed but enormous JPEG is
     * exactly the case that would otherwise exhaust memory.
     */
    private const MAX_PIXELS = 40_000_000;

    /**
     * Compress and store, returning the path on the given disk.
     *
     * Falls back to storing the original untouched if anything goes wrong.
     * A customer who has already paid must never lose their submission because
     * we could not re-encode their photo — a large file is a much smaller
     * problem than a rejected proof.
     */
    public function storeCompressed(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        try {
            $compressed = $this->compress($file);

            if ($compressed !== null) {
                $path = $directory . '/' . Str::random(40) . '.jpg';
                Storage::disk($disk)->put($path, $compressed);

                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('Image compression failed, storing original: ' . $e->getMessage(), [
                'original_name' => $file->getClientOriginalName(),
                'size'          => $file->getSize(),
            ]);
        }

        return $file->store($directory, $disk);
    }

    /**
     * The re-encoded JPEG bytes, or null when this file should be stored as-is.
     *
     * Returns null rather than throwing for the "not worth it" cases, so the
     * caller's fallback path is the same for both.
     */
    public function compress(UploadedFile $file): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null; // No GD: the caller stores the original.
        }

        $path = $file->getRealPath();

        if (! $path || ! is_readable($path)) {
            return null;
        }

        $info = @getimagesize($path);

        if ($info === false) {
            return null; // Not an image GD can read.
        }

        [$width, $height] = $info;

        if ($width * $height > self::MAX_PIXELS) {
            Log::warning('Image too large to compress safely; stored as uploaded.', [
                'dimensions' => $width . 'x' . $height,
            ]);

            return null;
        }

        $image = @imagecreatefromstring(file_get_contents($path));

        if ($image === false) {
            return null;
        }

        try {
            $image = $this->applyExifOrientation($image, $path, $info['mime'] ?? '');
            $image = $this->resizeToFit($image);

            return $this->encodeWithinTarget($image);
        } finally {
            imagedestroy($image);
        }
    }

    /**
     * Encode at the highest quality that fits the target, stopping at the
     * floor rather than degrading the image indefinitely.
     *
     * Most uploads clear the target on the first step, so the loop usually runs
     * once. Returns the last (smallest) attempt when none fit — a slightly
     * large file is a far better outcome than a rejected proof.
     */
    private function encodeWithinTarget(\GdImage $image): ?string
    {
        $encoded = null;

        foreach (self::QUALITY_STEPS as $quality) {
            ob_start();
            // EXIF is not carried over by imagejpeg, which is exactly what we
            // want — the GPS tags phones bury in photos go with it.
            imagejpeg($image, null, $quality);
            $encoded = ob_get_clean() ?: null;

            if ($encoded === null || strlen($encoded) <= self::TARGET_BYTES) {
                break;
            }
        }

        return $encoded;
    }

    /**
     * Rotate to match the camera's EXIF orientation tag.
     *
     * Only JPEGs carry one, and only when the exif extension is present; every
     * other case returns the image untouched.
     */
    private function applyExifOrientation(\GdImage $image, string $path, string $mime): \GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = $exif['Orientation'] ?? null;

        $degrees = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($degrees === 0) {
            return $image;
        }

        $rotated = @imagerotate($image, $degrees, 0);

        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    /**
     * Scale so the longest edge is at most MAX_EDGE. Images already smaller are
     * returned untouched — upscaling a small receipt would add bytes and no
     * legibility.
     *
     * The canvas is filled white first: a PNG screenshot with transparency
     * would otherwise flatten onto black in JPEG, which is unreadable.
     */
    private function resizeToFit(\GdImage $image): \GdImage
    {
        $width  = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        $scale = $longest > self::MAX_EDGE ? self::MAX_EDGE / $longest : 1.0;

        $targetWidth  = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));

        imagecopyresampled(
            $canvas, $image,
            0, 0, 0, 0,
            $targetWidth, $targetHeight,
            $width, $height
        );

        imagedestroy($image);

        return $canvas;
    }
}
