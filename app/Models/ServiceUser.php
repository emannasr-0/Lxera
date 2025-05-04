<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

use App\User;

class ServiceUser extends Pivot{
    use HasFactory;

    public $incrementing = true;
    protected $guarded = [];

    protected $table = 'service_user';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function bundleTransform()
    {
        return $this->hasOne(BundleTransform::class, 'service_request_id','id');
    }
    public function BridgingRequest()
    {
        return $this->hasOne(BridgingRequest::class, 'service_request_id','id');
    }

    public function bundleDelay()
    {
        return $this->hasOne(BundleDelay::class, 'service_request_id', 'id');
    }
}
