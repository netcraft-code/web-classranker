<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial;

use CustomFeature\ClassRanker\Models\Bookmark;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionResource extends JsonResource
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
            'id'                => $this->id,
            'quiz_id'           => $this->quiz_id,
            'question_text'     => $this->question_text,
            'question_solution' => $this->question_solution,
            'question_order'    => $this->question_order,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'options'           => QuizOptionResource::collection($this->whenLoaded('options')),
            'is_bookmarked'   => Bookmark::where([
                'customer_id'       => auth()->user()->id,
                'grade_id'          => auth()->user()->grade_id,
                'bookmarkable_id'   => $this->id,
                'bookmarkable_type' => get_class($this->resource),
            ])->exists(),
        ];
    }
}