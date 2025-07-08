<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\StudentRequirement;
use App\Models\Bundle;

class Student extends Model

{
    protected $table = "students";
    protected $guarded = [];
    public function registeredUser()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    // student Requirments reltion
    public function bundles()
    {
        return $this->belongsToMany(Bundle::class);
    }
    public function bundleStudent(){

        return $this->hasMany(BundleStudent::class,'student_id');

    }
    public function classBundleStudent($class_id){

        return $this->hasMany(BundleStudent::class,'student_id')->where("class_id",$class_id)->get();

    }
    public function excludedBundle(){
        return $this->belongsToMany(Bundle::class,'student_exception_certificate');

    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

}
