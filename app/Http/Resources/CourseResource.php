<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            "description"=> strip_tags($this->description),
            "requirements"=> $this->requirements,
            "price"=> $this->price,
            "status"=> $this->status,
            "duration"=> $this->duration,
            "applying_link"=>env('APP_URL') . "webinars/$this->id/apply",
            "start_date"=> dateTimeFormat($this->start_date, 'j M Y'),
            "created_at"=> dateTimeFormat($this->created_at, 'j M Y'),
        ];
        return $data;
    }
}
