<?php

namespace App\Models;

use App\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExceptionCertificate extends Model
{
    use HasFactory;
    protected $table = 'student_exception_certificate';

    // The primary key for the model
    protected $primaryKey = 'id';

    // Whether the model should be timestamped (created_at, updated_at)
    public $timestamps = true;

    // The attributes that are mass assignable
    protected $fillable = [
        'bundle_id',
        'student_id',
        'webinar_id',
    ];

    // Define relationships (optional)
    
    /**
     * Get the bundle that owns the StudentExceptionCertificate.
     */
    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }

    public function webinar()
    {
        return $this->belongsTo(Webinar::class, 'webinar_id');
    }

    /**
     * Get the student that owns the StudentExceptionCertificate.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
