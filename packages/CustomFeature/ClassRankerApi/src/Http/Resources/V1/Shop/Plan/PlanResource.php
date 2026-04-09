<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\Plan;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'id'         => $this->id,
            'name'       => $this->name,
            'code'       => $this->code,
            'description' => $this->description,
            'price'      => $this->price,
            'discount_price' => $this->discount_price,
            'duration_value' => $this->duration_value,
            'duration_type' => $this->duration_type,
            'features' => $this->features,
            'avatar'     => $this->avatar,
            'avatar_url' => $this->avatar_url,
            'sort_order' => $this->sort_order,
            'is_popular' => $this->is_popular,
            'status'     => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}