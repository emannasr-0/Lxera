<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InstallmentResource extends JsonResource
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
            "id" => $this->id,
            "title" => $this->title,
            "main_title" => $this->main_title,
            "description" => $this->description,
            "options" => $this->options,
            "bannar" => $this->bannar,
            "start_date" => dateTimeFormat($this->start_date, 'j M Y H:i'),
            "end_date" => dateTimeFormat($this->end_date, 'j M Y H:i'),
            "total_amount" => $this->totalPayments(),
            "upfront" => $this->upfront,
            "steps" => InstallmentStepResource::collection($this->steps),
        ];
    }
}
