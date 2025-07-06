<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsersNotAccessToContentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_users_not_access_content_lists');

        $query = User::where('access_content', false);

        $query = $this->filters($query, $request);

        $users = $query->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'pageTitle' => trans('update.users_do_not_have_access_to_the_content'),
            'users' => $users,
        ];

        return response()->json($data, 200,  [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    private function filters($query, $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $full_name = $request->get('full_name');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($full_name)) {
            $query->where('full_name', 'like', "%$full_name%");
        }

        return $query;
    }

    public function store(Request $request)
    {
        $this->authorize('admin_users_not_access_content_toggle');

        $data = $request->all();

        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($data['user_id']);

        $user->update([
            'access_content' => false
        ]);

        return response()->json([
            'status' => 'success',
            'msg' => 'User Added Successfully'
        ], 201);
    }

    public function active($url_name,$id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_users_not_access_content_toggle');

        $user = User::findOrFail($id);

        $user->update([
            'access_content' => true
        ]);

        $notifyOptions = [

        ];
        sendNotification('user_access_to_content', $notifyOptions, $user->id);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.content_access_was_enabled_for_the_user', ['user' => $user->full_name]),
            'status' => 'success'
        ];
        return response()->json($toastData);
    }
}
