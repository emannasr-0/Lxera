<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
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
            "seller" => UserResource::make($this->seller),
            "buyer" => UserResource::make($this->buyer),
            "order" => $this->order,
            "bundle" => ActiveBundleResource::make($this->bundle),
            "webinar" => $this->webinar,
            "service" => $this->service,
            "batch" => $this->class,
            "form_fee" => $this->form_fee,
            "payment_method" => $this->payment_method,
            "payment_email" => $this->payment_email,
            "type" => $this->type,
            "amount" => $this->amount,
            "total_amount" => $this->total_amount,
            "refund_at" => $this->refund_at,
            "message" => $this->message,


            
        ];
        
        return $data;
    }
}
