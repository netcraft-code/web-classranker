<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial;

use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
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
            'name'       => $this->title,
            'code'       => $this->code,
            'writer'     => $this->writer,
            'publisher'  => $this->publisher,
            'edition '   => $this->edition ,
            'publication_year'       => $this->publication_year ,
            'total_pages'=> $this->total_pages ,
            'avatar'     => $this->avatar,
            'avatar_url' => $this->avatar_url,
            'status'     => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}