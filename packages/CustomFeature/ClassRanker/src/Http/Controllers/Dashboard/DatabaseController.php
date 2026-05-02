<?php

namespace CustomFeature\ClassRanker\Http\Controllers\Dashboard;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\Http\Controllers\Controller;

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
    public function exportTable($table)
    {
        $this->authorizeAccess();

        // 🔐 Strict validation
        if (! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            abort(403);
        }

        if (! \Schema::hasTable($table)) {
            abort(404);
        }

        $data = DB::table($table)->get();

        $fileName = $table . '_' . now()->timestamp . '.json';
        $path = "exports/$fileName";

        Storage::disk('local')->put($path, $data->toJson(JSON_PRETTY_PRINT));

        return response()->download(storage_path("app/$path"));
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
}