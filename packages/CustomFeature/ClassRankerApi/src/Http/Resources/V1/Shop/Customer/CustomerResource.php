<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\Customer;

use CustomFeature\ClassRanker\Models\CustomerPlan;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {        
        return [
            'id'            => $this->id,
            'email'         => $this->email,
            'name'          => $this->name,
            'first_name'    => $this->first_name,
            'last_name'     => $this->last_name,
            'gender'        => $this->gender,
            'image'         => $this->image_url,
            'is_verified'   => $this->is_verified,
            'profile_complete' => $this->board_id && $this->grade_id,
            'board_id'      => $this->board_id,
            'board_name'    => $this->board?->name,
            'grade_id'      => $this->grade_id,
            'grade_name'    => $this->grade?->name,
            'date_of_birth' => $this->date_of_birth,
            'phone'         => $this->phone,
            'status'        => $this->status,
            'addresses'     => $this->when($this->addresses->first(), new CustomerAddressResource($this->addresses->first())),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'is_premium_user' => $this->isPremium() || $this->isPremiumRewarded(),
            'has_active_plan' => $this->isPremium(),
        ];
    }
}