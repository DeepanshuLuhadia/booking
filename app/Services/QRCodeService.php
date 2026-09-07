<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Vendor;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Facades\Storage;

/**
 * The shareable QR codes printed at a counter or handed to a customer.
 *
 * Rendered as JPEG, not SVG: these get downloaded and dropped into WhatsApp,
 * print shops, flyers and posters, and a fair number of those tools simply
 * refuse an SVG. The pixels are drawn here with GD rather than through
 * simple-qrcode's own image writer, because that one needs ext-imagick and the
 * servers we run on only have GD.
 */
class QRCodeService
{
    /** Rendered edge length, in pixels, before margin rounding. */
    private const SIZE = 500;

    /** Quiet zone, in modules. Below 2 the code stops scanning reliably. */
    private const MARGIN = 2;

    /** Module colour — primary-500. */
    private const FOREGROUND = [14, 165, 233];

    /**
     * Generate a unique QR code for a vendor and return the storage path.
     */
    public function generateForVendor(Vendor $vendor): string
    {
        $fileName = 'qrcodes/' . $vendor->slug . '.jpg';

        $this->store($fileName, route('vendor.show', $vendor->slug));

        return $fileName;
    }

    /**
     * Generate a unique QR code for an employee and return the storage path.
     */
    public function generateForEmployee(Employee $employee): string
    {
        $identifier = $employee->slug ?? $employee->id;
        $fileName = 'qrcodes/employee_' . $identifier . '.jpg';

        $this->store($fileName, route('employee.public.show', $identifier));

        $employee->update(['qr_code_path' => $fileName]);

        return $fileName;
    }

    /**
     * The stored path, regenerating only when there is nothing usable there.
     *
     * "Usable" includes the format: accounts created before the switch to JPEG
     * still point at an SVG that is present on disk, so an existence check
     * alone would leave them on the old file forever.
     */
    public function ensureForVendor(Vendor $vendor): string
    {
        if ($this->isUsable($vendor->qr_code_path)) {
            return $vendor->qr_code_path;
        }

        $vendor->qr_code_path = $this->generateForVendor($vendor);
        $vendor->save();

        return $vendor->qr_code_path;
    }

    /**
     * @see self::ensureForVendor()
     */
    public function ensureForEmployee(Employee $employee): string
    {
        if ($this->isUsable($employee->qr_code_path)) {
            return $employee->qr_code_path;
        }

        return $this->generateForEmployee($employee);
    }

    /**
     * Is this stored path a JPEG that is actually on disk?
     */
    private function isUsable(?string $path): bool
    {
        return filled($path)
            && str_ends_with(strtolower($path), '.jpg')
            && Storage::disk('public')->exists($path);
    }

    /**
     * Render $url and write it to the public disk, clearing any older SVG left
     * behind for the same code.
     */
    private function store(string $fileName, string $url): void
    {
        Storage::disk('public')->put($fileName, $this->renderJpeg($url));

        $legacySvg = preg_replace('/\.jpg$/', '.svg', $fileName);

        if ($legacySvg !== $fileName && Storage::disk('public')->exists($legacySvg)) {
            Storage::disk('public')->delete($legacySvg);
        }
    }

    /**
     * The encoded matrix painted onto a white canvas as JPEG bytes.
     *
     * Modules are drawn at a whole number of pixels and the canvas sized to
     * match, so no module lands on a half pixel — resampling a QR code is what
     * makes it stop scanning, and JPEG's blur on a hard edge is bad enough
     * without it.
     */
    private function renderJpeg(string $url): string
    {
        $matrix = Encoder::encode($url, ErrorCorrectionLevel::M(), 'UTF-8')->getMatrix();
        $modules = $matrix->getWidth();

        $moduleSize = max(1, (int) floor(self::SIZE / ($modules + self::MARGIN * 2)));
        $edge = ($modules + self::MARGIN * 2) * $moduleSize;

        $image = imagecreatetruecolor($edge, $edge);

        $white = imagecolorallocate($image, 255, 255, 255);
        $foreground = imagecolorallocate($image, ...self::FOREGROUND);

        imagefilledrectangle($image, 0, 0, $edge - 1, $edge - 1, $white);

        $offset = self::MARGIN * $moduleSize;

        for ($y = 0; $y < $matrix->getHeight(); $y++) {
            for ($x = 0; $x < $modules; $x++) {
                if ($matrix->get($x, $y) !== 1) {
                    continue;
                }

                $left = $offset + $x * $moduleSize;
                $top  = $offset + $y * $moduleSize;

                imagefilledrectangle(
                    $image,
                    $left,
                    $top,
                    $left + $moduleSize - 1,
                    $top + $moduleSize - 1,
                    $foreground
                );
            }
        }

        ob_start();
        // High quality on purpose: JPEG ringing around the finder patterns is
        // what costs a scan, and the file is still a few tens of KB.
        imagejpeg($image, null, 95);
        $jpeg = ob_get_clean();

        imagedestroy($image);

        return $jpeg;
    }
}
