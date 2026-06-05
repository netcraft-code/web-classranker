<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\Notification as NotificationContract;
use CustomFeature\ClassRanker\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Notification extends Model implements NotificationContract
{
    protected $table = 'push_notifications';

    protected $fillable = [
        'title',
        'text',
        'image',
        'redirect_type',
        'link',
        'path',
        'params',
    ];

    protected $casts = [
        'params' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['image_url'];

    /**
     * Get image url for the customer image.
     *
     * @return string|null
     */
    public function image_url()
    {
        if (! $this->image) {
            return;
        }

        return Storage::url($this->image);
    }

    /**
     * Get image URL accessor.
     */
    public function getImageUrlAttribute()
    {
        return $this->image_url();
    }
}