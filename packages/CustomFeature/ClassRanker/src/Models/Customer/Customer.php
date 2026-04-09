<?php

namespace CustomFeature\ClassRanker\Models\Customer;

use CustomFeature\Board\Models\Board;
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
}
