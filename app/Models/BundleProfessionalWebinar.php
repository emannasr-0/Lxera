<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleProfessionalWebinar extends Model
{
    protected $table = 'bundle_Professional_course';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function webinar()
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo('App\Models\Bundle', 'bundle_id', 'id');
    }
    public function getWebinarLearningPageUrl()

    {
        return url('panel/bundles/'.$this->bundle->id .'/course/learning/' . $this->webinar->id);
    }
}
