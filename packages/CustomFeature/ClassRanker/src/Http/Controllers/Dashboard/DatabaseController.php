<?php

namespace CustomFeature\ClassRanker\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\Http\Controllers\Controller;
use ZipArchive;

class DatabaseController extends Controller
{
    // 🔐 Admin Guard
    private function authorizeAccess()
    {
        if (! auth()->guard('admin')->check()) {
            abort(403);
        }
    }

    // 📊 List Tables
    public function tables()
    {
        $this->authorizeAccess();

        $dbName = env('DB_DATABASE');

        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($t) => array_values((array) $t)[0]);

        return view('class_ranker::system.tables', compact('tables'));
    }

    // 📥 Export Table (JSON)
    public function exportTable(Request $request, $table)
    {
        $this->authorizeAccess();

        // 🔐 Strict validation
        if (! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            abort(403);
        }

        if (! \Schema::hasTable($table)) {
            abort(404);
        }

        $format = $request->get('type', 'sql'); // json | sql

        // 🔹 JSON EXPORT
        if ($format === 'json') {
            $data = DB::table($table)->get();

            $fileName = $table.'_'.time().'.json';
            $path = "exports/$fileName";

            Storage::put($path, $data->toJson(JSON_PRETTY_PRINT));

            return response()->download(storage_path("app/$path"));
        }

        // 🔹 SQL EXPORT
        if ($format === 'sql') {

            return response()->streamDownload(function () use ($table) {
                $rows = DB::table($table)->get();
                $columns = Schema::getColumnListing($table);

                echo "DROP TABLE IF EXISTS `$table`;\n";

                $create = DB::select("SHOW CREATE TABLE `$table`")[0]->{'Create Table'};
                echo $create . ";\n\n";

                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        return is_null($value)
                            ? 'NULL'
                            : "'" . addslashes($value) . "'";
                    }, (array) $row);

                    echo "INSERT INTO `$table` (`"
                        . implode('`,`', $columns)
                        . "`) VALUES ("
                        . implode(',', $values)
                        . ");\n";
                }

            }, $table . '_' . time() . '.sql');
        }

        abort(400, 'Invalid export type');
    }

    // 📂 List Files (storage/app only)
    public function files()
    {
        $this->authorizeAccess();

        $files = Storage::disk('local')->allFiles();

        return view('class_ranker::system.files', compact('files'));
    }

    // 📥 Download File
    public function downloadFile($path)
    {
        $this->authorizeAccess();

        // 🔐 Security checks
        if (
            str_contains($path, '..') ||
            str_contains($path, '.env') ||
            str_contains($path, 'vendor')
        ) {
            abort(403);
        }

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path);
    }

    public function downloadStorageZip()
    {
        $this->authorizeAccess();

        $zipFileName = 'storage_public_'.time().'.zip';
        $zipPath = storage_path("app/$zipFileName");

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            $files = Storage::disk('public')->allFiles();

            foreach ($files as $file) {
                $fullPath = storage_path('app/public/'.$file);
                $zip->addFile($fullPath, $file);
            }

            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}