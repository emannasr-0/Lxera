<?php

namespace App\Models;

use App\BundleStudent;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyClass extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function enrollments(){
        return User::whereHas(
            'bundleSales',
            function ($query) {
                $query->where("class_id", $this->id)->groupBy('buyer_id');
            }
        )->get();
    }

    public function registerEnrollements(){

        return User::where(['role_name' => Role::$registered_user])->whereDoesntHave('student')->whereBetween('created_at', [strtotime($this->start_date), strtotime($this->end_date)])->get();
    }
    
    public function formFeeEnrollements(){

        return  User::whereHas('student')->whereHas('purchasedFormBundleUnique', function ($query){
            $query->where('class_id', $this->id);
        })->get();
    }
    public function bundleEnrollements(){
        return User::where(['role_name' => Role::$user])->whereHas('purchasedBundles', function ($query) {
            $query->where('class_id', $this->id)->where("payment_method", "!=", 'scholarship');
        })->get();
    }
    public function directRegisterEnrollements(){
        return User::whereHas('student.bundleStudent', function ($query) {
            $query->whereNull('class_id')->whereHas('bundle', function ($query) {
                $query->where('batch_id', $this->id);
            });
        })->get();
    }
    public function scholarshipEnrollements(){
        return User::where(['role_name' => Role::$user])->whereHas('purchasedBundles', function ($query)  {
            $query->where("payment_method", 'scholarship')->where('class_id', $this->id);
        })->get();
    }
    // public function enrollments(){
    //     return $this->hasMany(BundleStudent::class, 'class_id')->groupBy('student_id');
    // }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'class_id');
    }

}
