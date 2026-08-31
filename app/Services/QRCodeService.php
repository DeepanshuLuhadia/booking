<?php

namespace App\Services;

use App\Models\Vendor;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generate a unique QR code for a vendor and return the storage path.
     */
    public function generateForVendor(Vendor $vendor)
    {
        $url = route('vendor.show', $vendor->slug);
        $fileName = 'qrcodes/' . $vendor->slug . '.svg';

        $qrCode = QrCode::format('svg')
            ->size(500)
            ->margin(2)
            ->color(14, 165, 233) // primary-500
            ->generate($url);

        Storage::disk('public')->put($fileName, $qrCode);

        return $fileName;
    }

    /**
     * Generate a unique QR code for an employee and return the storage path.
     */
    public function generateForEmployee(\App\Models\Employee $employee)
    {
        $identifier = $employee->slug ?? $employee->id;
        $url = route('employee.public.show', $identifier);
        $fileName = 'qrcodes/employee_' . $identifier . '.svg';

        $qrCode = QrCode::format('svg')
            ->size(500)
            ->margin(2)
            ->color(14, 165, 233) // primary-500
            ->generate($url);

        Storage::disk('public')->put($fileName, $qrCode);

        $employee->update(['qr_code_path' => $fileName]);

        return $fileName;
    }
}
