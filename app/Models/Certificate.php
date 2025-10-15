<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Bundle;
use App\Models\Traits\FilterBySearchTrait;

class Certificate extends Model
{
    use FilterBySearchTrait;

    protected $table = "certificates";
    public $timestamps = false;
    protected $guarded = ['id'];

    public function quiz()
    {
        return $this->hasOne(Quiz::class, 'id', 'quiz_id');
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
        return $this->belongsTo(Webinar::class, 'webinar_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }

    protected static function boot()
    {
        parent::boot();
    }
}
