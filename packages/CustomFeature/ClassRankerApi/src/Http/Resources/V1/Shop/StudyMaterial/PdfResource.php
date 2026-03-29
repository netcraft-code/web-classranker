<?php

namespace CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial;

use Carbon\Carbon;
use CustomFeature\ClassRanker\Models\Bookmark;
use Illuminate\Http\Resources\Json\JsonResource;

class PdfResource extends JsonResource
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
            'bottom_description' => $this->bottom_description,
            'status'             => $this->status,
            'is_premium'         => $this->is_premium,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
            "item_id"            => $this->item_id,
            "item_title"         => $this->item_title,
            "pdf_path"           => $this->pdf_path,
            "pdf_path_url"       => $this->pdf_path_url,
            "position"           => $this->position,
            'item_created_at'    => Carbon::parse($this->item_created_at)->diffForHumans(),
            'is_bookmarked' => Bookmark::where([
                'customer_id'       => auth()->user()->id,
                'grade_id'          => auth()->user()->grade_id,
                'bookmarkable_id'   => $this->item_id,
                'bookmarkable_type' => \CustomFeature\Pdf\Models\PdfItem::class,
            ])->exists(),
        ];
    }
}