<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseCategoryResource extends JsonResource
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
        $data = [
            "id"=> $this->id,
            "title"=> $this->title,
            "status"=> $this->status,
            "courses"=> CourseResource::collection($this->webinars)
        ];

        return $data;
    }
}
