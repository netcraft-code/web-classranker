<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\CustomerDevice as CustomerDeviceContract;
use CustomFeature\ClassRanker\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;

class CustomerDevice extends Model implements CustomerDeviceContract
{
    protected $fillable = [
        'customer_id',
        'fcm_token',
        'device_name',
        'device_type',
        'app_version',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}