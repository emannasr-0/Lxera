<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class program extends Model
{
    use HasFactory;
    protected $guarded = [];


    function bundles(){
        return $this->hasMany('App\Models\bundle');
    }

}
