<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial;

use CustomFeature\ClassRanker\Models\Bookmark;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
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
            'id'                 => $this->id,
            'title'              => $this->title,
            'short_title'        => $this->short_title,
            'slug'               => $this->slug,
            'top_description'    => $this->top_description,
            'content'            => $this->content,
            'bottom_description' => $this->bottom_description,
            'status'             => $this->status,
            'is_premium'         => $this->is_premium,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
            'is_bookmarked'      => Bookmark::where([
                'customer_id'       => auth()->user()->id,
                'grade_id'          => auth()->user()->grade_id,
                'bookmarkable_id'   => $this->id,
                'bookmarkable_type' => get_class($this->resource),
            ])->exists(),
        ];
    }
}