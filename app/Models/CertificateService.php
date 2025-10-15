<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateService extends Model
{
    use HasFactory;

    // Define the table name if it's not the plural of the model name
    protected $table = 'certificate_service';

    // Define the fillable attributes for mass assignment
    protected $guarded =[];
    
    function serviceRequest(){
        return $this->belongsTo('App\Models\ServiceUser','service_request_id','id');
    }
    function user(){
        return $this->belongsTo('App\User','user_id','id');
    }
 
}
