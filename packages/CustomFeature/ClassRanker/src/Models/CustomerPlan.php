<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\CustomerPlan as CustomerPlanContract;
use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;

class CustomerPlan extends Model implements CustomerPlanContract
{
    protected $table = 'customer_plans';
 
    protected $fillable = [
        'customer_id',
        'plan_id',
        'payu_txnid',
        'payu_mihpayid',
        'payu_hash',
        'amount_paid',
        'currency',
        'status',
        'starts_at',
        'expires_at',
    ];
 
    protected $casts = [
        'amount_paid' => 'decimal:2',
        'starts_at'   => 'datetime',
        'expires_at'  => 'datetime',
    ];
 
    // ─── Relationships ────────────────────────────────────────────────────────
 
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
 
    public function plan()
    {
        return $this->belongsTo(PlanProxy::modelClass(), 'plan_id');
    }
 
    // ─── Accessors ────────────────────────────────────────────────────────────
 
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active'
            && $this->expires_at
            && $this->expires_at->isFuture();
    }
 
    public function getDaysRemainingAttribute(): int
    {
        if (! $this->is_active) return 0;
 
        return (int) now()->diffInDays($this->expires_at);
    }
 
    // ─── Scopes ───────────────────────────────────────────────────────────────
 
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('expires_at', '>', now());
    }
}