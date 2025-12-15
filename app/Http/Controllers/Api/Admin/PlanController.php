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
  
public function index($url_name)
{
    //dd($url_name);
    // نجيب الـ organization من الـ url_name
    $organization = Organization::where('url_name', $url_name)->first();

    if (!$organization) {
        return response()->json(['message' => 'Organization not found'], 404);
    }

    // عدد المستخدمين في هذه الـ organization
    $usersCountForOrg = User::where('organization_id', $organization->id)->count();

    // نجيب كل الخطط
    $plans = Plan::all();

    // نحضر البيانات مع عدد المستخدمين والمتبقي
    $plans = $plans->map(function ($plan) use ($organization, $usersCountForOrg) {

        // لو دي هي الخطة الحالية للـ organization -> استخدم عدد المستخدمين
        if ($plan->id == $organization->plan_id) {
            $usedUsers = $usersCountForOrg;
        } else {
            // باقي الخطط مش متفعّلة للـ organization دي
            $usedUsers = 0;
        }

        $maxUsers = (int) ($plan->max_users ?? 0);
        $availableUsers = max($maxUsers - $usedUsers, 0);

        return [
            'id'              => $plan->id,
            'name'            => $plan->name,
            'name_ar'         => $plan->name_ar,
            'description'     => $plan->description,
            'price'           => $plan->price,
            'max_users'       => $maxUsers,
            'used_users'      => $usedUsers,        // عدد مستخدمي الخطة
            'available_users' => $availableUsers,   // المتاح للإضافة
            'max_bundles'     => $plan->max_bundles,
            'max_webinars'    => $plan->max_webinars,
            'start_date'      => $plan->start_date,
            'end_date'        => $plan->end_date,
            'is_active'       => $plan->is_active,
        ];
    });

    return response()->json([
        'success' => true,
        'data'    => [
            'plans' => $plans,
        ],
    ], 200);
}

    public function update($url_name, Request $request, $id)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $plan = Plan::findOrFail($id);

            $this->validate($request, [
                'name' => 'sometimes|string|min:3|max:255',
                'name_ar' => 'sometimes|string|min:3|max:255',
                'description' => 'sometimes|string|min:3|max:255',
                'price' => 'sometimes|integer',
                'max_users' => 'sometimes|integer',
                'max_bundles' => 'sometimes|integer',
                'max_webinars' => 'sometimes|integer',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
                "is_active" => 'sometimes|integer'
            ]);

            $data = $request->all();

            $plan->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Category Updated Successfully',
                'data' => $plan
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $formattedErrors = [];

            $currentLocale = app()->getLocale();

            foreach ($e->errors() as $field => $messages) {
                $data = $e->validator->getData();
                $rules = [$field => $e->validator->getRules()[$field]];

                app()->setLocale('ar');
                $arValidator = Validator::make($data, $rules);
                $arMessage = $arValidator->errors()->first($field);

                app()->setLocale('en');
                $enValidator = Validator::make($data, $rules);
                $enMessage = $enValidator->errors()->first($field);

                $formattedErrors[$field] = [
                    'ar' => $arMessage,
                    'en' => $enMessage
                ];
            }

            app()->setLocale($currentLocale);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $formattedErrors
            ], 422);
        }
    }


    public function store($url_name, Request $request)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'name_ar' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable|string',
                'max_users' => 'nullable|integer',
                'max_bundles' => 'nullable|integer',
                'max_webinars' => 'nullable|integer',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $validData = $request->all();
            $plan = Plan::create($validData);

            return response()->json([
                'status' => 'success',
                'message' => 'Plan created successfully',
                'data' => $plan
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $formattedErrors = [];

            $currentLocale = app()->getLocale();

            foreach ($e->errors() as $field => $messages) {
                $data = $e->validator->getData();
                $rules = [$field => $e->validator->getRules()[$field]];

                app()->setLocale('ar');
                $arValidator = Validator::make($data, $rules);
                $arMessage = $arValidator->errors()->first($field);

                app()->setLocale('en');
                $enValidator = Validator::make($data, $rules);
                $enMessage = $enValidator->errors()->first($field);

                $formattedErrors[$field] = [
                    'ar' => $arMessage,
                    'en' => $enMessage
                ];
            }

            app()->setLocale($currentLocale);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $formattedErrors
            ], 422);
        }
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
