<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BundleBridging extends Model
{
    use HasFactory;

    protected $table = 'bundle_bridging';

    protected $guarded = [];


    public function mainBundle()
    {
        return $this->belongsTo('App\Models\bundle', 'bridging_id', 'id');
    }
    public function fromBundle()
    {
        return $this->belongsTo('App\Models\bundle', 'from_bundle_id', 'id');
    }
    public function toBundle()
    {
        return $this->belongsTo('App\Models\bundle', 'to_bundle_id', 'id');
    }

}
