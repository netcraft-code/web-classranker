<?php

namespace CustomFeature\ClassRanker\Models\Customer;

use CustomFeature\Board\Models\Board;
use CustomFeature\ClassRanker\Models\CustomerPlan;
use CustomFeature\ClassRanker\Models\Plan;
use CustomFeature\ClassRanker\Models\PremiumAccess;
use CustomFeature\Grade\Models\Grade;
use Webkul\Customer\Models\Customer as BaseCustomer;

class Customer extends BaseCustomer
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'email',
        'phone',
        'password',
        'api_token',
        'token',
        'customer_group_id',
        'channel_id',
        'subscribed_to_news_letter',
        'status',
        'is_verified',
        'is_suspended',
        'board_id',
        'grade_id',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function isPremium()
    {
        $customerPlans = CustomerPlan::with('plan')
            ->where('customer_id', $this->id)
            ->latest()
            ->get();
 
        $activePlan = $customerPlans->first(fn($cp) => $cp->is_active);

        return (bool) $activePlan;
    }

    public function isPremiumRewarded()
    {
        $access = PremiumAccess::where('customer_id', $this->id)
            ->where('expires_at', '>', now())
            ->first();

        return (bool) $access;
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'customer_plans')
            ->withPivot([
                'status',
                'starts_at',
                'expires_at',
                'amount_paid',
                'currency'
            ])
            ->withTimestamps();
    }
}
