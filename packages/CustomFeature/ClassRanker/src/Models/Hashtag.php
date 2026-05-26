<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\Hashtag as HashtagContract;
use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model implements HashtagContract
{
    protected $fillable = ['name', 'slug', 'status'];

    protected $casts = ['status' => 'boolean'];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function discussions()
    {
        return $this->belongsToMany(Discussion::class, 'discussion_hashtag');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}