<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupService
{
    /**
     * Menghasilkan download cadangan database (SQLite atau MySQL/MariaDB).
     */
    public function downloadBackup(): BinaryFileResponse|StreamedResponse
    {
        $connection = config('database.default');
        $dateSuffix = date('Y-m-d_His');

        if ($connection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if (!File::exists($dbPath)) {
                $dbPath = database_path('database.sqlite');
            }

            if (File::exists($dbPath)) {
                $backupFilename = "ruanggtk-backup-{$dateSuffix}.sqlite";
                return response()->download($dbPath, $backupFilename, [
                    'Content-Type' => 'application/x-sqlite3',
                ]);
            }
        }

        // MySQL / MariaDB / Fallback: Ekspor SQL lengkap berbasis PDO
        $backupFilename = "ruanggtk-backup-{$dateSuffix}.sql";

        return response()->streamDownload(function () {
            $this->streamSqlDump();
        }, $backupFilename, [
            'Content-Type' => 'application/sql',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Menghasilkan dump SQL penuh untuk MySQL/MariaDB atau SQLite secara streaming.
     */
    protected function streamSqlDump(): void
    {
        $driver = DB::getDriverName();
        $pdo = DB::connection()->getPdo();
        $timestamp = date('Y-m-d H:i:s');

        echo "-- ═══════════════════════════════════════════════════════════\n";
        echo "-- Ruang GTK Multi-Tenant Platform Database Backup\n";
        echo "-- Waktu Backup : {$timestamp} WIB\n";
        echo "-- Engine       : {$driver}\n";
        echo "-- Versi Core   : Laravel " . app()->version() . "\n";
        echo "-- ═══════════════════════════════════════════════════════════\n\n";

        if ($driver === 'mysql') {
            echo "SET NAMES utf8mb4;\n";
            echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";

            $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
            $dbKey = 'Tables_in_' . DB::getDatabaseName();

            foreach ($tables as $tableObj) {
                $table = $tableObj->$dbKey ?? reset($tableObj);
                echo "-- -------------------------------------------------------------\n";
                echo "-- Table structure for `{$table}`\n";
                echo "-- -------------------------------------------------------------\n";
                echo "DROP TABLE IF EXISTS `{$table}`;\n";

                $createStmt = DB::select("SHOW CREATE TABLE `{$table}`");
                if (!empty($createStmt)) {
                    $createSql = $createStmt[0]->{'Create Table'} ?? '';
                    echo $createSql . ";\n\n";
                }

                // Dump data
                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    echo "-- Dumping data for table `{$table}`\n";
                    echo "INSERT INTO `{$table}` VALUES \n";
                    $totalRows = $rows->count();
                    $rowIndex = 0;

                    foreach ($rows as $row) {
                        $rowIndex++;
                        $values = [];
                        foreach ((array) $row as $val) {
                            if (is_null($val)) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $pdo->quote($val);
                            }
                        }
                        $line = "(" . implode(', ', $values) . ")";
                        echo $line . ($rowIndex < $totalRows ? ",\n" : ";\n\n");
                    }
                }
            }

            echo "SET FOREIGN_KEY_CHECKS = 1;\n";
            echo "-- Backup selesai dengan sukses.\n";
        } else {
            // SQLite SQL Dump
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($tables as $tableObj) {
                $table = $tableObj->name;
                echo "-- Table: {$table}\n";
                $create = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
                if (!empty($create) && !empty($create[0]->sql)) {
                    echo $create[0]->sql . ";\n";
                }

                $rows = DB::table($table)->get();
                foreach ($rows as $row) {
                    $cols = array_keys((array) $row);
                    $escapedCols = array_map(fn($c) => "`{$c}`", $cols);
                    $values = [];
                    foreach ((array) $row as $val) {
                        $values[] = is_null($val) ? 'NULL' : $pdo->quote($val);
                    }
                    echo "INSERT INTO `{$table}` (" . implode(', ', $escapedCols) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                echo "\n";
            }
        }
    }

    /**
     * Pembersihan database, sesi kadaluarsa, temporary cache, dan optimasi disk.
     */
    public function cleanAndOptimize(): array
    {
        $driver = DB::getDriverName();
        $cleanedItems = [];

        // 1. Bersihkan sesi database kadaluarsa jika tabel sessions ada
        try {
            if (DB::getSchemaBuilder()->hasTable('sessions')) {
                $deletedSessions = DB::table('sessions')
                    ->where('last_activity', '<', now()->subDays(7)->timestamp)
                    ->delete();
                if ($deletedSessions > 0) {
                    $cleanedItems[] = "{$deletedSessions} sesi kedaluwarsa dihapus";
                }
            }
        } catch (\Throwable) {}

        // 2. Bersihkan failed jobs lama jika ada
        try {
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $deletedJobs = DB::table('failed_jobs')
                    ->where('failed_at', '<', now()->subDays(30))
                    ->delete();
                if ($deletedJobs > 0) {
                    $cleanedItems[] = "{$deletedJobs} log antrean gagal dibersihkan";
                }
            }
        } catch (\Throwable) {}

        // 3. Reclaim Disk Space / VACUUM / OPTIMIZE
        try {
            if ($driver === 'sqlite') {
                DB::statement('VACUUM;');
                $cleanedItems[] = "Eksekusi VACUUM SQLite (defragmentasi storage)";
            } elseif ($driver === 'mysql') {
                $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
                $dbKey = 'Tables_in_' . DB::getDatabaseName();
                foreach ($tables as $tObj) {
                    $tName = $tObj->$dbKey ?? reset($tObj);
                    @DB::statement("OPTIMIZE TABLE `{$tName}`");
                }
                $cleanedItems[] = "Optimasi indeks & tabel MySQL selesai";
            }
        } catch (\Throwable $e) {
            $cleanedItems[] = "Optimasi storage: " . $e->getMessage();
        }

        // 4. Bersihkan Cache Laravel Framework
        if (!app()->environment('testing')) {
            try {
                Artisan::call('cache:clear');
                Artisan::call('view:clear');
                $cleanedItems[] = "Cache view & application cache dibersihkan";
            } catch (\Throwable) {}
        }

        return [
            'success' => true,
            'details' => $cleanedItems,
            'summary' => 'Pembersihan database dan defragmentasi selesai. ' . count($cleanedItems) . ' operasi pemeliharaan dieksekusi.',
        ];
    }
}
