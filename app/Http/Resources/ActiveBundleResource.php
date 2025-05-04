<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActiveBundleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public $show = false;

    public function toArray($request)
    {
        return [
            "id"=> $this->id,
            "slug"=> $this->slug,
            "title"=> $this->title,
            "category"=> [
                "id" => $this->category->id,
                "slug"=> $this->category->slug,
                "title"=> $this->category->title
            ],
            "status"=> $this->status,
            "has_certificate"=> $this->has_certificate,
            "early_enroll"=> $this->early_enroll,
            "start_date"=> $this->start_date,
            "content_table"=> $this->content_table,


        ];
    }
}
