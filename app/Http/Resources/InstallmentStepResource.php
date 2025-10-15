<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InstallmentStepResource extends JsonResource
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
            "amount" => $this->amount,
            "order" => $this->order,
            "deadline" => $this->deadline,
            


        ];
    }
}
