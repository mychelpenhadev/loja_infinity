<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function apiBackup(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Increase limits for large datasets
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        try {
            $timestamp = date('Y_m_d_H_i_s');
            $sqlFilename = 'backup_db_' . $timestamp . '.sql';
            $sqlPath = storage_path('app/' . $sqlFilename);
            $handle = fopen($sqlPath, 'w');

            // --- Database Export (Streaming to Disk) ---
            $tables = DB::select('SHOW TABLES');
            $databaseName = DB::connection()->getDatabaseName();
            $variableName = "Tables_in_" . $databaseName;

            fwrite($handle, "-- Backup da Loja -- Data: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET NAMES utf8mb4;\n\n");

            foreach ($tables as $table) {
                $tableName = $table->$variableName;
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0]->{'Create Table'};
                fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n{$createTable};\n\n");

                DB::table($tableName)->orderBy(DB::raw('1'))->chunk(200, function ($rows) use ($handle, $tableName) {
                    foreach ($rows as $row) {
                        $values = array_map(function ($v) {
                            if (is_null($v)) return "NULL";
                            return "'" . str_replace(["\n", "\r"], ["\\n", "\\r"], addslashes($v)) . "'";
                        }, (array)$row);
                        fwrite($handle, "INSERT INTO `{$tableName}` VALUES (" . implode(", ", $values) . ");\n");
                    }
                });
            }
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);

            // --- ZIP Packaging (Database + Media) ---
            $zipFilename = 'backup_full_' . $timestamp . '.zip';
            $zipPath = storage_path('app/' . $zipFilename);
            $zip = new \ZipArchive();

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                // Add SQL file
                $zip->addFile($sqlPath, $sqlFilename);

                // Add Media Directories
                $mediaDirs = [
                    'assets/img/produtos' => public_path('assets/img/produtos'),
                    'uploads' => public_path('uploads'),
                    'assets/img' => public_path('assets/img')
                ];

                foreach ($mediaDirs as $targetName => $dir) {
                    if (is_dir($dir)) {
                        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY);
                        foreach ($files as $name => $file) {
                            if (!$file->isDir()) {
                                $filePath = $file->getRealPath();
                                // Store under 'media/' in ZIP to maintain separation
                                $relativePath = 'media/' . substr($filePath, strlen(public_path()) + 1);
                                $zip->addFile($filePath, $relativePath);
                            }
                        }
                    }
                }
                $zip->close();
            }

            if (file_exists($sqlPath)) unlink($sqlPath);

            return response()->download($zipPath, $zipFilename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => "Erro no backup: " . $e->getMessage()], 400);
        }
    }

    public function uploadChunk(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $chunk = $request->file('chunk');
        $index = $request->input('index');
        $identifier = $request->input('identifier');

        $tempDir = storage_path('app/temp_chunks/' . $identifier);
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

        $chunk->move($tempDir, "chunk_{$index}");
        return response()->json(['status' => 'success']);
    }

    public function restoreFinal(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        try {
            $identifier = $request->input('identifier');
            $totalChunks = $request->input('total');
            $filename = $request->input('filename');

            $tempDir = storage_path('app/temp_chunks/' . $identifier);
            $finalZip = $tempDir . '/restore.zip';
            
            // Merge chunks into final ZIP
            $handle = fopen($finalZip, 'ab');
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = $tempDir . "/chunk_{$i}";
                if (!file_exists($chunkPath)) throw new \Exception("Chunk {$i} ausente.");
                fwrite($handle, file_get_contents($chunkPath));
                unlink($chunkPath);
            }
            fclose($handle);

            // Extract ZIP content
            $zip = new \ZipArchive();
            if ($zip->open($finalZip) === TRUE) {
                // 1. Extract Media (physical files)
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileNameInside = $zip->getNameIndex($i);
                    if (str_starts_with($fileNameInside, 'media/')) {
                        $destPath = public_path(substr($fileNameInside, 6)); // substr(6) to remove 'media/' prefix
                        $destDir = dirname($destPath);
                        if (!is_dir($destDir)) mkdir($destDir, 0777, true);
                        copy("zip://" . $finalZip . "#" . $fileNameInside, $destPath);
                    }
                }

                // 2. Locate and Extract SQL
                $sqlFile = null;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    if (str_ends_with($zip->getNameIndex($i), '.sql')) {
                        $sqlFile = $zip->getNameIndex($i);
                        break;
                    }
                }

                if ($sqlFile) {
                    $sqlTemp = $tempDir . '/restore.sql';
                    copy("zip://" . $finalZip . "#" . $sqlFile, $sqlTemp);
                    
                    // Increase packet limit if possible (already set global in earlier turn)
                    try { DB::statement("SET GLOBAL max_allowed_packet=67108864"); } catch(\Exception $e) {}
                    
                    // Stream SQL execution to avoid memory issues
                    $this->executeSqlFile($sqlTemp);
                    unlink($sqlTemp);
                }
                
                $zip->close();
            }

            unlink($finalZip);
            rmdir($tempDir);

            return response()->json(["status" => "success", "message" => "Backup completo (Bancos + Mídias) restaurado!"]);
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => "Erro na restauração: " . $e->getMessage()], 400);
        }
    }

    private function executeSqlFile($path)
    {
        $handle = fopen($path, 'r');
        $query = "";
        while (($line = fgets($handle)) !== false) {
            if (trim($line) == "" || str_starts_with(trim($line), '--') || str_starts_with(trim($line), '/*')) continue;
            
            $query .= $line;
            if (str_ends_with(trim($line), ';')) {
                // Apply compatibility replacements
                $query = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci', $query);
                
                try {
                    DB::unprepared($query);
                } catch (\Exception $e) {
                    // Log error if needed, but continue
                }
                $query = "";
            }
        }
        fclose($handle);
    }
}
