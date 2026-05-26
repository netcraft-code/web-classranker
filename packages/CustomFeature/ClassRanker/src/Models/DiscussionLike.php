<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;

class DiscussionLike extends Model
{
    protected $fillable = ['discussion_id', 'customer_id'];

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}