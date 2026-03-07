<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionFaqResource extends JsonResource
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
            'id'          => $this->id,
            'question_id' => $this->question_id,
            'question'    => $this->question,
            'answer'      => $this->answer,
            'order'       => $this->order,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}