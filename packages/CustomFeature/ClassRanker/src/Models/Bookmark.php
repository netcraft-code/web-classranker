<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\Bookmark as BookmarkContract;
use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;

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