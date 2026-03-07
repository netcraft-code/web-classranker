<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizOptionResource extends JsonResource
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
            'id'               => $this->id,
            'quiz_question_id' => $this->quiz_question_id,
            'option_text'      => $this->option_text,
            'is_correct'       => $this->is_correct,
            'option_order'     => $this->option_order,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}