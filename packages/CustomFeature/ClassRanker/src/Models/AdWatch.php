<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\AdWatch as AdWatchContract;
use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;

class AdWatch extends Model implements AdWatchContract
{
    protected $fillable = ['customer_id', 'watched_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}