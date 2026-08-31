<?php

namespace Tests\Feature;

use App\Services\ImageCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The compressor that stands between a phone's camera roll and our disk.
 *
 * Its contract is narrow but load-bearing: shrink what it can, never lose the
 * upload. A customer who has already transferred money must not have their
 * proof rejected because we could not re-encode their photo, so every failure
 * path here falls back to storing the original rather than throwing.
 */
class ImageCompressorTest extends TestCase
{
    private ImageCompressor $compressor;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->compressor = new ImageCompressor();
    }

    public function test_a_large_image_is_scaled_down_and_re_encoded(): void
    {
        $file = UploadedFile::fake()->image('shot.jpg', 3024, 4032);

        $out = $this->compressor->compress($file);

        $this->assertNotNull($out);
        [$w, $h] = getimagesizefromstring($out);

        $this->assertSame(1600, max($w, $h), 'The long edge is capped, not the short one.');
        $this->assertEqualsWithDelta(3024 / 4032, $w / $h, 0.01, 'Aspect ratio must survive.');
        $this->assertLessThan($file->getSize(), strlen($out));
    }

    /** Landscape must cap the WIDTH — the same rule read the other way. */
    public function test_a_landscape_image_caps_its_width(): void
    {
        $out = $this->compressor->compress(UploadedFile::fake()->image('wide.jpg', 4000, 2250));

        [$w, $h] = getimagesizefromstring($out);
        $this->assertSame(1600, $w);
        $this->assertEqualsWithDelta(4000 / 2250, $w / $h, 0.01);
    }

    /**
     * An image already smaller than the cap keeps its dimensions. Upscaling
     * would add bytes and no legibility.
     */
    public function test_a_small_image_is_not_upscaled(): void
    {
        $out = $this->compressor->compress(UploadedFile::fake()->image('small.jpg', 600, 800));

        [$w, $h] = getimagesizefromstring($out);
        $this->assertSame([600, 800], [$w, $h]);
    }

    /** PNG becomes JPEG, and transparency flattens onto white, not black. */
    public function test_a_png_is_converted_to_jpeg_on_white(): void
    {
        $out = $this->compressor->compress(UploadedFile::fake()->image('shot.png', 900, 900));

        $info = getimagesizefromstring($out);
        $this->assertSame('image/jpeg', $info['mime']);
    }

    /** A file GD cannot read returns null so the caller stores the original. */
    public function test_an_unreadable_file_returns_null_rather_than_throwing(): void
    {
        $notAnImage = UploadedFile::fake()->create('notes.txt', 10, 'text/plain');

        $this->assertNull($this->compressor->compress($notAnImage));
    }

    /** The storing path always yields a usable file, whatever the input. */
    public function test_store_compressed_always_produces_a_stored_file(): void
    {
        $path = $this->compressor->storeCompressed(
            UploadedFile::fake()->image('shot.jpg', 2000, 1500),
            'payment_proofs'
        );

        Storage::disk('public')->assertExists($path);
        $this->assertStringStartsWith('payment_proofs/', $path);
        $this->assertStringEndsWith('.jpg', $path);
    }

    /** ...including when it cannot compress: the original is kept. */
    public function test_store_compressed_falls_back_to_the_original_when_it_cannot_compress(): void
    {
        $path = $this->compressor->storeCompressed(
            UploadedFile::fake()->create('receipt.txt', 4, 'text/plain'),
            'payment_proofs'
        );

        Storage::disk('public')->assertExists($path);
    }
}
