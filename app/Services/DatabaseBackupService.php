<?php

namespace App\Services;

use Illuminate\Http\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File as Filesystem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Dumps the app's database to a gzipped file and prunes old ones.
 *
 * mysqldump credentials go through a --defaults-extra-file rather than
 * --password=... on argv, so the DB password never shows up in a `ps aux`
 * listing on the shared EB instance.
 */
class DatabaseBackupService
{
    public function disk(): string
    {
        return config('backup.disk', 'local');
    }

    public function directory(): string
    {
        return trim((string) config('backup.path', 'backups'), '/');
    }

    public function retentionDays(): int
    {
        return (int) config('backup.retention_days', 7);
    }

    /**
     * Create a new backup and return its filename.
     */
    public function create(): string
    {
        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}");
        $driver = $connection['driver'] ?? null;

        $tempFile = tempnam(sys_get_temp_dir(), 'db-backup-');
        $gzPath = $tempFile . '.gz';

        try {
            $extension = match ($driver) {
                'mysql', 'mariadb' => 'sql',
                'sqlite' => 'sqlite',
                default => throw new RuntimeException("Database backups are not supported for the '{$driver}' driver."),
            };

            match ($driver) {
                'mysql', 'mariadb' => $this->dumpMysql($connection, $tempFile),
                'sqlite' => Filesystem::copy($connection['database'], $tempFile),
            };

            $this->gzip($tempFile, $gzPath);

            $filename = sprintf('backup-%s.%s.gz', now()->format('Y-m-d_His'), $extension);
            $disk = Storage::disk($this->disk());
            $disk->makeDirectory($this->directory());
            $disk->putFileAs($this->directory(), new File($gzPath), $filename);

            return $filename;
        } finally {
            @unlink($tempFile);
            @unlink($gzPath);
        }
    }

    /**
     * List backups newest first.
     */
    public function list(): Collection
    {
        $disk = Storage::disk($this->disk());

        return collect($disk->files($this->directory()))
            ->filter(fn (string $path) => str_ends_with($path, '.gz'))
            ->map(fn (string $path) => [
                'name'     => basename($path),
                'size'     => $disk->size($path),
                'modified' => Carbon::createFromTimestamp($disk->lastModified($path)),
            ])
            ->sortByDesc('modified')
            ->values();
    }

    /**
     * Delete backups older than the retention window. Returns how many were removed.
     */
    public function pruneOld(): int
    {
        $cutoff = now()->subDays($this->retentionDays());
        $disk = Storage::disk($this->disk());
        $deleted = 0;

        foreach ($disk->files($this->directory()) as $path) {
            if (Carbon::createFromTimestamp($disk->lastModified($path))->lt($cutoff)) {
                $disk->delete($path);
                $deleted++;
            }
        }

        return $deleted;
    }

    private function dumpMysql(array $connection, string $outputPath): void
    {
        $optionsFile = tempnam(sys_get_temp_dir(), 'db-backup-opt-');
        file_put_contents($optionsFile, sprintf(
            "[client]\nuser=\"%s\"\npassword=\"%s\"\nhost=\"%s\"\nport=\"%s\"\n",
            $connection['username'] ?? '',
            $connection['password'] ?? '',
            $connection['host'] ?? '127.0.0.1',
            $connection['port'] ?? 3306,
        ));
        chmod($optionsFile, 0600);

        try {
            $process = new Process([
                'mysqldump',
                '--defaults-extra-file=' . $optionsFile,
                '--single-transaction',
                '--quick',
                '--skip-lock-tables',
                $connection['database'],
            ]);
            $process->setTimeout(600);
            $process->run(function (string $type, string $buffer) use ($outputPath) {
                if ($type === Process::OUT) {
                    file_put_contents($outputPath, $buffer, FILE_APPEND);
                }
            });

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        } finally {
            @unlink($optionsFile);
        }
    }

    private function gzip(string $source, string $destination): void
    {
        $in = fopen($source, 'rb');
        $out = gzopen($destination, 'wb9');

        while (! feof($in)) {
            gzwrite($out, fread($in, 512 * 1024));
        }

        fclose($in);
        gzclose($out);
    }
}
