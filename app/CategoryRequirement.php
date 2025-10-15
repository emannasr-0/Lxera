<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\User;

class CategoryRequirement extends Model

{
    protected $table = "categories_requirements";
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }


}
