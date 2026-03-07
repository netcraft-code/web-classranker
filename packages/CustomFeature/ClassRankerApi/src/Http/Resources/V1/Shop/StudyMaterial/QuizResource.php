<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
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
            'title'      => $this->title,
            'slug'       => $this->slug,
            'description' => $this->description,
            'status'     => $this->status,
            'is_premium' => $this->is_premium,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'questions'  => QuizQuestionResource::collection($this->whenLoaded('questions')),
            'question_count' => $this->questions()->count(),
        ];
    }
}