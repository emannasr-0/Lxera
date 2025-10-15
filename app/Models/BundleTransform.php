<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BundleTransform extends Model
{
    use HasFactory;

    protected $guarded =[];

    function fromBundle(){
        return $this->belongsTo('App\Models\Bundle','from_bundle_id','id');
    }
    function toBundle(){
        return $this->belongsTo('App\Models\Bundle','to_bundle_id','id');
    }
    function user(){
        return $this->belongsTo('App\User','user_id','id');
    }
    function serviceRequest(){
        return $this->belongsTo('App\Models\ServiceUser','service_request_id','id');
    }
}
