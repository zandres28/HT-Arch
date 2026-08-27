<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Respaldo y restauración de la aplicación:
 *  - export(): volcado SQL de la base de datos + archivos de evidencias/entregables en un ZIP.
 *  - import(): extrae el ZIP, restaura la base de datos y los archivos.
 */
class BackupService
{
    protected string $storageApp;
    protected array $folders = ['attachments', 'deliverables'];

    public function __construct()
    {
        $this->storageApp = storage_path('app');
    }

    public function export(): string
    {
        $tmp = storage_path('app/backups');
        if (!is_dir($tmp)) {
            mkdir($tmp, 0755, true);
        }

        $stamp = now()->format('Y-m-d_His');
        $zipPath = $tmp . DIRECTORY_SEPARATOR . "ht-arch_{$stamp}.zip";

        // 1) Volcado SQL
        $sqlPath = $tmp . DIRECTORY_SEPARATOR . "dump_{$stamp}.sql";
        $this->dumpDatabase($sqlPath);

        // 2) Empaquetar SQL + carpetas de archivos
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo de respaldo.');
        }

        $zip->addFile($sqlPath, 'database.sql');

        foreach ($this->folders as $folder) {
            $dir = $this->storageApp . DIRECTORY_SEPARATOR . $folder;
            if (is_dir($dir)) {
                $this->addDirectory($zip, $dir, $folder);
            }
        }

        $zip->close();
        @unlink($sqlPath);

        return $zipPath;
    }

    public function import(string $zipPath): void
    {
        $extract = storage_path('app/backups/restore_' . Str::random(8));
        mkdir($extract, 0755, true);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('El archivo de respaldo no es un ZIP válido.');
        }
        $zip->extractTo($extract);
        $zip->close();

        try {
            // 1) Restaurar base de datos
            $sqlFile = $extract . DIRECTORY_SEPARATOR . 'database.sql';
            if (!is_file($sqlFile)) {
                throw new \RuntimeException('El respaldo no contiene database.sql.');
            }
            $this->restoreDatabase($sqlFile);

            // 2) Limpiar carpetas actuales y restaurar archivos
            foreach ($this->folders as $folder) {
                $target = $this->storageApp . DIRECTORY_SEPARATOR . $folder;
                if (is_dir($target)) {
                    File::deleteDirectory($target, true);
                }
                $source = $extract . DIRECTORY_SEPARATOR . $folder;
                if (is_dir($source)) {
                    File::copyDirectory($source, $target);
                }
            }
        } finally {
            File::deleteDirectory($extract, true);
        }
    }

    protected function dumpDatabase(string $sqlPath): void
    {
        $dump = $this->mysqldumpPath();
        if (!$dump) {
            throw new \RuntimeException('No se encontró mysqldump.exe en el sistema.');
        }

        $cfg = Config::get('database.connections.mysql');
        $db = $cfg['database'];
        $host = $cfg['host'];
        $port = $cfg['port'] ?? 3306;
        $user = $cfg['username'];
        $pass = $cfg['password'] ?? '';

        $cmd = '"' . $dump . '" --host=' . escapeshellarg($host)
            . ' --port=' . escapeshellarg((string) $port)
            . ' --user=' . escapeshellarg($user);
        if ($pass !== '') {
            $cmd .= ' --password=' . escapeshellarg($pass);
        }
        $cmd .= ' --single-transaction --no-tablespaces --skip-lock-tables '
            . escapeshellarg($db) . ' > "' . $sqlPath . '"';

        $this->run($cmd);
    }

    protected function restoreDatabase(string $sqlPath): void
    {
        $mysql = $this->mysqlPath();
        if (!$mysql) {
            throw new \RuntimeException('No se encontró mysql.exe en el sistema.');
        }

        $cfg = Config::get('database.connections.mysql');
        $db = $cfg['database'];
        $host = $cfg['host'];
        $port = $cfg['port'] ?? 3306;
        $user = $cfg['username'];
        $pass = $cfg['password'] ?? '';

        $cmd = '"' . $mysql . '" --host=' . escapeshellarg($host)
            . ' --port=' . escapeshellarg((string) $port)
            . ' --user=' . escapeshellarg($user);
        if ($pass !== '') {
            $cmd .= ' --password=' . escapeshellarg($pass);
        }
        $cmd .= ' --default-character-set=utf8mb4 ' . escapeshellarg($db)
            . ' < "' . $sqlPath . '"';

        $this->run($cmd);
    }

    protected function run(string $cmd): void
    {
        $output = [];
        $code = 0;
        exec($cmd . ' 2>&1', $output, $code);
        if ($code !== 0) {
            throw new \RuntimeException('Falló el comando de base de datos: ' . implode("\n", array_slice($output, -5)));
        }
    }

    protected function mysqldumpPath(): ?string
    {
        return $this->findBinary('mysqldump.exe');
    }

    protected function mysqlPath(): ?string
    {
        return $this->findBinary('mysql.exe');
    }

    protected function findBinary(string $name): ?string
    {
        $candidates = [
            'D:\\laragon_6\\bin\\mysql',
        ];
        foreach ($candidates as $base) {
            if (!is_dir($base)) {
                continue;
            }
            foreach (glob($base . '\\*\\bin\\' . $name) as $path) {
                return $path;
            }
        }
        // Buscar en PATH
        $which = shell_exec('where ' . $name . ' 2>nul');
        if ($which) {
            $first = strtok(trim($which), "\n");
            if ($first && is_file($first)) {
                return $first;
            }
        }
        return null;
    }

    protected function addDirectory(\ZipArchive $zip, string $dir, string $zipPath): void
    {
        $files = File::allFiles($dir);
        foreach ($files as $file) {
            $zip->addFile($file->getPathname(), $zipPath . '/' . $file->getRelativePathname());
        }
    }
}
