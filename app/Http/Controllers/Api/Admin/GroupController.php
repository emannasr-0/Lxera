<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\Models\Group;
use App\Models\GroupRegistrationPackage;
use App\Models\GroupUser;
use App\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_group_list');

        $groups = Group::query()->with(['groupUsers']);
        $filters = $request->input('filters');

        if (isset($filters['group_name'])) {
            $groups = $groups->where('name', 'like', '%' . $filters['group_name'] . '%');
        }

        $data = [
            'pageTitle' => trans('admin/pages/groups.group_list_page_title'),
            'groups' => $groups->get(),
            'group_name' => $filters['group_name'] ?? '',
        ];

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_group_create');

        $this->validate($request, [
            'users' => 'array',
            'name' => 'required',
        ]);

        $data = $request->all();
        $data['created_at'] = time();
        $data['creator_id'] = auth()->user()->id;
        unset($data['_token']);

        $group = Group::create($data);

        $users = $request->input('users');

        if (!empty($users)) {
            foreach ($users as $userId) {
                if (GroupUser::where('user_id', $userId)->first()) {
                    continue;
                }

                GroupUser::create([
                    'group_id' => $group->id,
                    'user_id' => $userId,
                    'created_at' => time(),
                ]);

                $notifyOptions = [
                    '[u.g.title]' => $group->name,
                ];
                sendNotification('change_user_group', $notifyOptions, $userId);
                sendNotification('add_to_user_group', $notifyOptions, $userId);
            }
        }

        return response()->json([
            'status' => 'success',
            'msg' => 'Group Added Successfully'
        ], 201);
    }

    public function update($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'This organization was not found.'], 404);
        }

        $this->authorize('admin_group_edit');

        $this->validate($request, [
            'users' => 'array',
            'percent' => 'nullable',
            'name' => 'sometimes',
        ]);

        $group = Group::findOrFail($id);

        $data = $request->all();
        unset($data['_token']);

        $group->update($data);

        $users = $request->input('users');

        $group->groupUsers()->delete();

        if (!empty($users)) {
            foreach ($users as $userId) {
                GroupUser::create([
                    'group_id' => $group->id,
                    'user_id' => $userId,
                    'created_at' => time(),
                ]);

                $notifyOptions = [
                    '[u.g.title]' => $group->name,
                ];
                sendNotification('change_user_group', $notifyOptions, $userId);
                sendNotification('add_to_user_group', $notifyOptions, $userId);
            }
        }

        return response()->json([
            'status' => 'success',
            'msg' => 'Group Updated Successfully'
        ], 200);
    }

    public function destroy($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'This organization was not found.'], 404);
        }

        $this->authorize('admin_group_delete');

        $group = Group::find($id);
        if (!$group) {
            return response()->json(['message' => 'Group not found.'], 404);
        }

        $group->delete();

        $toastData = [
            'title' => 'حذف مجموعة دورة',
            'msg' => 'تم الحذف بنجاح',
            'status' => 'success',
        ];

        return response()->json($toastData);
    }

    public function groupRegistrationPackage(Request $request, $id)
    {
        $this->validate($request, [
            'instructors_count' => 'nullable|numeric',
            'students_count' => 'nullable|numeric',
            'courses_capacity' => 'nullable|numeric',
            'courses_count' => 'nullable|numeric',
            'meeting_count' => 'nullable|numeric',
        ]);

        $group = Group::findOrFail($id);

        $data = $request->all();

        GroupRegistrationPackage::updateOrCreate([
            'group_id' => $group->id,
        ], [
            'instructors_count' => $data['instructors_count'] ?? null,
            'students_count' => $data['students_count'] ?? null,
            'courses_capacity' => $data['courses_capacity'] ?? null,
            'courses_count' => $data['courses_count'] ?? null,
            'meeting_count' => $data['meeting_count'] ?? null,
            'status' => $data['status'],
            'created_at' => time(),
        ]);

        return redirect()->back();
    }
}
