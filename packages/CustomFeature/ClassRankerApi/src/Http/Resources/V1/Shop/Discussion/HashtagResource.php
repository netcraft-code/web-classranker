<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\Discussion;

use Illuminate\Http\Resources\Json\JsonResource;

class HashtagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $creatorName = 'Student';

        if ($this->creator_type && str_contains($this->creator_type, 'Admin')) {

            $creatorName = 'Study Rankers';

        } elseif ($this->creator) {

            $creatorName = trim(
                ($this->creator->first_name ?? '') . ' ' .
                ($this->creator->last_name ?? '')
            );

            $creatorName = $creatorName ?: 'Student';
        }
        
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'status'     => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}