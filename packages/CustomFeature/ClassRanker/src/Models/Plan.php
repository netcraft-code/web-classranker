<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\Plan as PlanContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;

class Plan extends Model implements PlanContract
{
    use HasFactory;
 
    protected $fillable = [
        'name',
        'code',
        'description',
        'price',
        'discount_price',
        'duration_value',
        'duration_type',
        'features',
        'avatar',
        'sort_order',
        'is_popular',
        'status',
    ];
 
    protected $casts = [
        'price'          => 'decimal:2',
        'discount_price' => 'decimal:2',
        'features'       => 'array',
        'is_popular'     => 'boolean',
        'status'         => 'boolean',
    ];
 
    // ─── Relationships ────────────────────────────────────────────────────────
 
    public function customerPlans()
    {
        return $this->hasMany(CustomerPlan::class, 'plan_id');
    }
 
    // ─── Accessors ────────────────────────────────────────────────────────────
 
    /**
     * Effective price (discount_price if set, else price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->discount_price ?? $this->price;
    }
 
    /**
     * Discount percentage.
     */
    public function getDiscountPercentAttribute(): int
    {
        if (! $this->discount_price || $this->discount_price >= $this->price) {
            return 0;
        }
 
        return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
    }
 
    /**
     * Human-readable duration: "1 Month", "3 Months", "1 Year"
     */
    public function getDurationLabelAttribute(): string
    {
        $label = $this->duration_value . ' ' . ucfirst(rtrim($this->duration_type, 's'));
 
        if ($this->duration_value > 1) {
            $label .= 's';
        }
 
        return $label;
    }
 
    /**
     * Avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }
 
    // ─── Scopes ───────────────────────────────────────────────────────────────
 
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
 
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }
}