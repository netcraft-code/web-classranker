<?php

namespace CustomFeature\ClassRanker\Http\Controllers\Hashtag;

use CustomFeature\ClassRanker\DataGrids\HashtagDataGrid;
use CustomFeature\ClassRanker\Repositories\HashtagRepository;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;

class HashtagController extends Controller
{
    public function __construct(
        protected HashtagRepository $hashtagRepository
    ) {}

    public function index()
    {
        if (request()->ajax()) {
            return app(HashtagDataGrid::class)->toJson();
        }

        return view('class_ranker::hashtags.index');
    }

    public function create()
    {
        return view('class_ranker::hashtags.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:100',
            'slug'   => 'required|string|max:100|unique:hashtags,slug',
            'status' => 'nullable|boolean',
        ]);

        $this->hashtagRepository->create([
            'name'   => $request->name,
            'slug'   => $request->slug,
            'status' => $request->boolean('status'),
        ]);

        session()->flash('success', 'Hashtag created successfully.');

        return redirect()->route('admin.hashtags.index');
    }

    public function edit(int $id)
    {
        $hashtag = $this->hashtagRepository->findOrFail($id);

        return view('class_ranker::hashtags.edit', compact('hashtag'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name'   => 'required|string|max:100',
            'slug'   => 'required|string|max:100|unique:hashtags,slug,' . $id,
            'status' => 'nullable|boolean',
        ]);

        $this->hashtagRepository->update([
            'name'   => $request->name,
            'slug'   => $request->slug,
            'status' => $request->boolean('status'),
        ], $id);

        session()->flash('success', 'Hashtag updated successfully.');

        return redirect()->route('admin.hashtags.index');
    }

    public function destroy(int $id)
    {
        try {
            $this->hashtagRepository->delete($id);
            return response()->json(['message' => 'Hashtag deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}