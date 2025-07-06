<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\Models\Api\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::get();
        $data = [
            'plans' => $plans
        ];

        return response()->json([
            'success' => true,
            'message' => $data
        ], 200);
    }

    public function update($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $plan = Plan::findOrFail($id);

        $this->validate($request, [
            'name' => 'sometimes|min:3|max:255',
            'name_ar' => 'sometimes|min:3|max:255',
            'description' => 'sometimes|min:3|max:255',
            'price' => 'sometimes|integer',
            'max_users' => 'sometimes|integer',
            'max_bundles' => 'sometimes|integer',
            'max_webinars' => 'sometimes|integer',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date'
        ]);

        $data = $request->all();

        $plan->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Category Updated Successfully',
        ]);
    }


    public function store($url_name, Request $request)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'string'],
            'max_users' => ['nullable', 'integer'],
            'max_bundles' => ['nullable', 'integer'],
            'max_webinars' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ], [
            'name.required' => 'Plan name is required',
            'name_ar.required' => 'Arabic Plan name is required',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $plan = new Plan();
        $plan->name = $request->name;
        $plan->name_ar = $request->name_ar;
        $plan->description = $request->description;
        $plan->type = 'Enterprise';
        $plan->max_users = $request->max_users;
        $plan->max_bundles = $request->max_bundles;
        $plan->max_webinars = $request->max_webinars;
        $plan->start_date = $request->start_date;
        $plan->end_date = $request->end_date;
        $plan->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Plan created successfully',
            'data' => $plan
        ], 201);
    }

    public function makeActive($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $plan = Plan::find($id);

        if (!$plan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Plan not found'
            ], 404);
        }

        DB::table('plans')->update(['is_active' => false]);

        DB::table('plans')->where('id', $id)->update(['is_active' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Plan set as active'
        ]);
    }

    public function destroy($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $plan = Plan::find($id);

        if (!$plan) {
            return response()->json(['message' => 'This Plan is Not found'], 404);
        }

        $plan->delete();

        return response()->json([
            'status' => 'success',
            'msg' => 'Plan Deleted Successfully'
        ]);
    }
}
