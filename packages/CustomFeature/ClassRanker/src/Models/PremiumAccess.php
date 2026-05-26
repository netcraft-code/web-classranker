<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\PremiumAccess as PremiumAccessContract;
use CustomFeature\ClassRanker\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;

class PremiumAccess extends Model implements PremiumAccessContract
{
    protected $fillable = ['customer_id', 'expires_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}