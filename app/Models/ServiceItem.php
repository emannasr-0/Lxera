<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    use HasFactory;

    protected $table = 'service_items';

    // Specify the fillable fields if you want to use mass assignment
    protected $fillable = [
        'bundle_id',
        'webinar_id',
        'service_id',
    ];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function webinar()
    {
        return $this->belongsTo(Webinar::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }


}
