<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\Bookmark as BookmarkContract;
use CustomFeature\ClassRanker\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model implements BookmarkContract
{
    protected $fillable = ['customer_id', 'grade_id', 'bookmarkable_id', 'bookmarkable_type'];

    public function bookmarkable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}