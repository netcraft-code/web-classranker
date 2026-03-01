<?php

namespace CustomFeature\ClassRanker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\Http\Controllers\Controller;

class TempUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB
            'type' => 'required|in:video,thumbnail,pdf',
        ]);

        $file = $request->file('file');
        $type = $request->input('type');

        $folder = match($type) {
            'video'     => 'temp/videos',
            'thumbnail' => 'temp/thumbnails',
            'pdf'       => 'temp/pdfs',
        };

        $path = $file->store($folder, 'public');

        return response()->json([
            'success'  => true,
            'path'     => $path,
            'url'      => Storage::disk('public')->url($path),
            'filename' => $file->getClientOriginalName(),
        ]);
    }
}