<?php
namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\DiscussionBlock as DiscussionBlockContract;
use CustomFeature\ClassRanker\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;

class DiscussionBlock extends Model implements DiscussionBlockContract
{
    protected $fillable = ['customer_id', 'blocked_by_id', 'reason'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}