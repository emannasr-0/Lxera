<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebinarAssignmentAttachment extends Model
{
    protected $table = 'webinar_assignment_attachments';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function getDownloadUrl()
    {
        return "/course/assignment/{$this->assignment_id}/download/{$this->id}/attach";
    }

    public function getFileSize()
    {
        $size = null;

        $file_path = public_path($this->attach);

        if (file_exists($file_path)) {
            $size = formatSizeUnits(filesize($file_path));
        }

        return $size;
    }
    public function getFileNameAttribute()
{
    return $this->attach ? basename($this->attach) : null;
}

public function getFileUrlAttribute()
{
    // بناء الرابط حسب المسار الصحيح
    return asset('store/assignments/attachments/' . $this->file_name);
}
public function chapter()
{
    return $this->belongsTo('App\Models\WebinarChapter', 'chapter_id', 'id');
}


}
