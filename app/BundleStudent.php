<?php

namespace App;

use App\StudentRequirement;
use App\Student;
use App\Models\Bundle;
use App\Models\StudyClass;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BundleStudent extends Pivot
{
    public function studentRequirement()
    {
        return $this->hasOne(StudentRequirement::class, "bundle_student_id");
    }

    public function student()
    {
        return $this->belongsTo(Student::class, "student_id");
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, "bundle_id");
    }


    public function class(){
        return $this->belongsTo(StudyClass::class, "class_id");
    }
}
