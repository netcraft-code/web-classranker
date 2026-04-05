<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionItemResource extends JsonResource
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
            'question_id'     => $this->question_id,
            'question_number' => $this->question_number,
            'question_title'  => $this->question_title,
            'question'        => $this->question,
            'answer'          => $this->answer,
            'page_number'     => $this->page_number,
            'video_solution'  => $this->video_solution,
            'order'           => $this->order,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'is_bookmarked'   => $this->is_bookmarked,
        ];
    }
}