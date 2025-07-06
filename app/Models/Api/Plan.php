<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 
        'name_ar', 
        'description', 
        'type', 
        'price', 
        'max_users', 
        'max_bundles', 
        'max_webinars', 
        'start_date', 
        'end_date'
    ];
}
