<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BackupController extends Controller
{
    public function index(DatabaseBackupService $backups)
    {
        return view('admin.backups.index', [
            'backups'       => $backups->list(),
            'retentionDays' => $backups->retentionDays(),
        ]);
    }

    /**
     * Generate a backup right now, on demand from the admin panel.
     */
    public function store(DatabaseBackupService $backups)
    {
        try {
            $filename = $backups->create();
            $backups->pruneOld();
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }

        return back()->with('success', "Backup created: {$filename}");
    }

    public function download(string $filename, DatabaseBackupService $backups): StreamedResponse
    {
        $disk = Storage::disk($backups->disk());
        $path = $backups->directory() . '/' . $filename;

        abort_unless($disk->exists($path), 404);

        return $disk->download($path, $filename);
    }

    public function destroy(string $filename, DatabaseBackupService $backups)
    {
        $disk = Storage::disk($backups->disk());
        $path = $backups->directory() . '/' . $filename;

        abort_unless($disk->exists($path), 404);
        $disk->delete($path);

        return back()->with('success', 'Backup deleted.');
    }
}
