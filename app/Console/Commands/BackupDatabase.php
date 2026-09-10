<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump the database to a gzipped file and delete backups older than the retention window';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backups): int
    {
        try {
            $filename = $backups->create();
            $this->info("Backup created: {$filename}");
        } catch (Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $deleted = $backups->pruneOld();
        if ($deleted > 0) {
            $this->info("Pruned {$deleted} backup(s) older than {$backups->retentionDays()} days.");
        }

        return self::SUCCESS;
    }
}
