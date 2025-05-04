<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Student;
use App\User;

class StudentRequirement extends Model

{
    const pending = 'pending';
    const approved = 'approved';
    const rejected = 'rejected';
    protected $table = "student_requirement";
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function admin()
    {
        return $this->belongsTo(User::class,"approved_by");
    }
    public function bundleStudent()
    {
        return $this->belongsTo(BundleStudent::class,"bundle_student_id");
    }

}
