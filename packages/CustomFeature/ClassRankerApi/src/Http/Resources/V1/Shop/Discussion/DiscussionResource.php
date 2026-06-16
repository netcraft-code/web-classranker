<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\Discussion;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscussionResource extends JsonResource
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
        
        $canModerate = false;

        if ($this->creator_type && str_contains($this->creator_type, 'Admin')) {
            $creatorName = 'Study Rankers';
        } elseif ($this->creator) {
            $creatorName = trim(
                ($this->creator->first_name ?? '') . ' ' .
                ($this->creator->last_name ?? '')
            );

            $canModerate = $this->creator->id == $this->creator_id;

            $creatorName = $creatorName ?: 'Student';
        }
        
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'description'    => $this->description,
            'creator_name'   => $creatorName,
            'board'          => $this->board?->name,
            'grade'          => $this->grade?->name,
            'subject'        => $this->subject?->name,
            'book'           => $this->book?->title,
            'chapter'        => $this->chapter?->title,
            'views_count'    => $this->views_count,
            'likes_count'    => $this->likes_count,
            'comments_count' => $this->comments_count,
            'status'         => $this->status,
            'created_at'     => $this->created_at->diffForHumans(),
            'is_liked'       => $this->is_liked,
            'is_resolved'    => $this->is_resolved,
            'can_moderate'   => $canModerate,
            'hashtags'       => HashtagResource::collection($this->whenLoaded('hashtags')),
            'comments'       => CommentResource::collection($this->whenLoaded('questions')),
        ];
    }
}