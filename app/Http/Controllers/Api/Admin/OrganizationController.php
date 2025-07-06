<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\Models\Api\Plan;
use App\Models\Api\User;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index() {
        $organization = Organization::all();
        $plan = Plan::where('is_active', 1)->get();

        $data = [
            'User' => auth()->user(),
            'Organization Data' => $organization,
            'Organization Plan' => $plan,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
