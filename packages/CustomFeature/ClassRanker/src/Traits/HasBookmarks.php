<?php

namespace CustomFeature\ClassRanker\Traits;

use CustomFeature\Video\Models\Bookmark;

trait HasBookmarks
{
    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }
}