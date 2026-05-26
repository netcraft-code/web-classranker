<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\AdWatch as AdWatchContract;
use CustomFeature\ClassRanker\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;

class AdWatch extends Model implements AdWatchContract
{
    protected $fillable = ['customer_id', 'watched_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}