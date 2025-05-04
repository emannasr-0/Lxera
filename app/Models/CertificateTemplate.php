<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Support\Facades\DB;
class CertificateTemplate extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = "certificates_templates";
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public $translatedAttributes = ['title', 'body'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getBodyAttribute()
    {
        return getTranslateAttributeValue($this, 'body');
    }

    public function getRtlAttribute()
    {
        return getTranslateAttributeValue($this, 'rtl');
    }
    public function bundle()
    {
        return $this->belongsToMany(Bundle::class);
    }

    public function webinar()
    {
        return $this->belongsToMany(Webinar::class);
    }
    
    
    public function sales()
    {
        return $this->hasMany('App\Models\Sale', 'certificate_template_id', 'id')
            ->whereNull('refund_at')
            ->where('type', 'cerftificate');
    }
    
    public function getImage($id)
    {
        $img=CertificateTemplate::where('id',$id)->first();
        return $img->image;
    }
    public function getTitle($id)
    {
        $title = DB::table('certificate_template_translations')
        ->where('certificate_template_id', $id)
        ->value('title');

        return $title;
    }
}
