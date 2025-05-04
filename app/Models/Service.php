<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\User;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description','price', 'apply_link', 'review_link', 'details', 'created_by', 'status', 'start_date', 'end_date','target'];


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class,"service_items");
    }

    public function webinars()
    {
        return $this->belongsToMany(Webinar::class,"service_items");
    }


    public function users(){
        return $this->belongsToMany(User::class)
                    ->using(ServiceUser::class)
                    ->withPivot(['id','status', 'approved_by', 'message', 'content'])
                    ->withTimestamps()
                    ->orderBy('service_user.created_at', 'desc');
    }
}
