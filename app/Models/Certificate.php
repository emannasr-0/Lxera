<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Bundle;
class Certificate extends Model
{
    protected $table = "certificates";
    public $timestamps = false;
    protected $guarded = ['id'];

    public function quiz()
    {
        return $this->hasOne('App\Models\Quiz', 'id', 'quiz_id');
    }

    public function student()
    {
        return $this->hasOne('App\User', 'id', 'student_id');
    }

    public function quizzesResult()
    {
        return $this->hasOne('App\Models\QuizzesResult', 'id', 'quiz_result_id');
    }

    public function webinar()
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }


    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id'); // Adjust the foreign key if necessary
    }
    
    protected static function boot()
    {
        parent::boot();

     
    }
    
    
}
