<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\PremiumAccess as PremiumAccessContract;
use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;

class PremiumAccess extends Model implements PremiumAccessContract
{
    protected $fillable = ['customer_id', 'expire_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}