<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            "slug"=> $this->slug,
            "status"=> $this->status,
            "title"=> $this->title,
            "activeBundles"=> ActiveBundleResource::collection($this->activeBundles)
        ];
        if(empty($this->parent_id)){
            $data["ActiveSubCategories"] = CategoryResource::collection($this->ActiveSubCategories);
        }
        return $data;
    }
}
