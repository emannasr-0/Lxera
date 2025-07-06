<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class OrganizationNotificationsController extends Controller
{
    public function index()
    {
        $notification = Notification::where('type', 'organizations')->get();
        return response()->json([
            'status' => 'success',
            'data' => $notification
        ]);
    }
}
