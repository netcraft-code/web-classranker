<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VideoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->item_id,   // video_item id
            'video_id'    => $this->id,         // parent video id
            'title'       => $this->item_title, // video item title
            'video_url'   => $this->video_link
                                ? Storage::disk('public')->url($this->video_link)
                                : null,
            'thumbnail'   => $this->thumbnail
                                ? Storage::disk('public')->url($this->thumbnail)
                                : null,
            'duration'    => $this->duration,
            'position'    => $this->position,

            // Parent video info
            'video_title'       => $this->title,
            'short_title'       => $this->short_title,
            'top_description'   => $this->top_description,
            'bottom_description'=> $this->bottom_description,
            'is_premium'        => $this->is_premium,
            'created_at'        => $this->created_at,
        ];
    }
}