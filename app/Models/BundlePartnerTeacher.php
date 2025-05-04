<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundlePartnerTeacher extends Model
{
    protected $table = 'bundle_partner_teacher';

    public $timestamps = false;

    protected $guarded = ['id'];

    public function teacher()
    {
        return $this->belongsTo('App\User', 'teacher_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id', 'id');
    }
}

