<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\RecentlyViewed as RecentlyViewedContract;
use CustomFeature\ClassRanker\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;

class RecentlyViewed extends Model implements RecentlyViewedContract
{
    protected $fillable = [
        'customer_id',
        'grade_id',
        'viewable_id',
        'viewable_type',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function viewable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}