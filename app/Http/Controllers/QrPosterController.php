<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\QRCodeService;
use App\Services\QrPosterService;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Downloadable JPEG posters built around the QR codes QRCodeService already
 * generates for vendors and employees. Nothing here touches QR generation —
 * each method fetches the existing, already-stored QR image and hands it to
 * QrPosterService, which only draws around it.
 */
class QrPosterController extends Controller
{
    public function vendor(QRCodeService $qrCodes, QrPosterService $posters)
    {
        $vendor = auth()->user()->vendor;
        abort_if(!$vendor, 404);

        $qrPath = $qrCodes->ensureForVendor($vendor);
        $jpeg = $posters->forVendor($vendor, $qrPath);

        return $this->download($jpeg, 'QR-' . Str::slug($vendor->business_name) . '.jpg');
    }

    public function employee(Employee $employee, QRCodeService $qrCodes, QrPosterService $posters)
    {
        $vendor = auth()->user()->vendor;
        abort_if(!$vendor || $employee->vendor_id !== $vendor->id, 403);

        $qrPath = $qrCodes->ensureForEmployee($employee);
        $jpeg = $posters->forEmployee($employee, $qrPath);

        return $this->download($jpeg, 'QR-' . Str::slug($employee->name) . '.jpg');
    }

    public function employeeSelf(QRCodeService $qrCodes, QrPosterService $posters)
    {
        $employee = auth()->user()->employee;
        abort_if(!$employee, 404);

        $qrPath = $qrCodes->ensureForEmployee($employee);
        $jpeg = $posters->forEmployee($employee, $qrPath);

        return $this->download($jpeg, 'QR-' . Str::slug($employee->name) . '.jpg');
    }

    private function download(string $jpeg, string $filename): Response
    {
        return response($jpeg, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
