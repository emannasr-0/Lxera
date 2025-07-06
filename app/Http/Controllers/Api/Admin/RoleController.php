<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Section;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('admin_roles_list');

        $roles = Role::with('users')
            ->orderBy('created_at', 'asc')
            ->get();

        $data = [
            'pageTitle' => trans('admin/pages/roles.page_lists_title'),
            'roles' => $roles,
        ];

        return response()->json($data, 200,  [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_roles_create');

        $this->validate($request, [
            'name' => 'required|min:3|max:64|unique:roles,name',
            'caption' => 'required|min:3|max:64|unique:roles,caption',
        ]);

        $data = $request->all();

        $role = Role::create([
            'name' => $data['name'],
            'caption' => $data['caption'],
            'is_admin' => (!empty($data['is_admin']) and $data['is_admin'] == 'on'),
            'created_at' => time(),
        ]);

        if ($role->is_admin and $request->has('permissions')) {
            $this->storePermission($role, $data['permissions']);
        }
        Cache::forget('sections');

        return response()->json([
            'status' => 'success',
            'msg' => 'Role Added Successfully'
        ], 201);
    }

    public function update($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_roles_edit');

        $role = Role::find($id);

        $this->validate($request, [
            'name' => 'required',
            'caption' => 'required',
        ]);

        $data = $request->all();

        $role->update([
            'name' => $data['name'],
            'caption' => $data['caption'],
            'is_admin' => ((!empty($data['is_admin']) and $data['is_admin'] == 'on') or $role->name == Role::$admin),
        ]);

        User::where('role_id', $role->id)->update(['role_name' => $role->name]);

        if ($role->is_admin) {

            $sectionIds = $request->input('permissions', []);
            $permissionsData = array_fill_keys($sectionIds, ['allow' => true]);

            // Sync the permissions
            $role->sections()->sync($permissionsData);
        } else {
            Permission::where('role_id', '=', $role->id)->delete();
        }

        Cache::forget('sections');

        return response()->json([
            'status' => 'success',
            'msg' => 'Permission Updated Successfully'
        ]);
    }

    public function destroy($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $this->authorize('admin_roles_delete');

        $role = Role::find($id);
        if ($role->id !== 2) {
            $role->delete();
        }

        return response()->json([
            'status' => 'success',
            'msg' => 'Role Deleted Successfully'
        ]);
    }

    public function storePermission($role, $sections)
    {
        $sectionsId = Section::whereIn('id', $sections)->pluck('id');
        $permissions = [];
        foreach ($sectionsId as $section_id) {
            $permissions[] = [
                'role_id' => $role->id,
                'section_id' => $section_id,
                'allow' => true,
            ];
        }
        Permission::insert($permissions);
    }
}
