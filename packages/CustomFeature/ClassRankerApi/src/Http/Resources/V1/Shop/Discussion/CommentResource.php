<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\Discussion;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'id'              => $this->id,
            'discussion_id'   => $this->discussion_id,
            'customer_id'     => $this->customer_id,
            'body'            => $this->body,
            'likes_count'     => $this->likes_count,
            'status'          => $this->status,
            'created_at'      => $this->created_at->diffForHumans(),
            'updated_at'      => $this->updated_at->diffForHumans(),
            'deleted_at'      => $this->deleted_at?->diffForHumans(),
            'deleted_by_type' => $this->deleted_by_type,
            'deleted_by_id'   => $this->deleted_by_id,
            'edited_at'       => $this->edited_at?->diffForHumans(),
            'is_liked'        => $this->is_liked,
            'has_active_plan' => $this->has_active_plan,
            'images'          => $this->images,
            'customer'        => [
                "id"         => $this->customer?->id,
                "first_name" => $this->customer?->first_name,
                "last_name"  => $this->customer?->last_name,
                "image"      => $this->customer?->image,
                "image_url"  => $this->customer?->image_url,
            ],
        ];
    }
}