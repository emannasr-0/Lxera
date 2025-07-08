<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\Models\Role;
use App\Models\Support;
use App\Models\SupportConversation;
use App\Models\SupportDepartment;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportsController extends Controller
{
    public function index()
    {
        $this->authorize('admin_supports_list');

        $supports = Support::with(['conversations', 'user', 'bundle', 'webinar'])
            ->orderBy('created_at', 'desc')
            ->get();
        $supports->transform(function ($support) {
            $support->translated_status = trans('panel.' . $support->status);
            return $support;
        });

        $statusOptions = [];
        $statuses = ['open', 'close', 'replied', 'supporter_replied'];
        foreach ($statuses as $status) {
            $statusOptions[] = [
                'value' => $status,
                'label' => trans('panel.' . $status),
            ];
        }
        return response()->json([
            'pageTitle' => trans('admin/pages/users.supports'),
            'status_options' => $statusOptions,
            'supports' => $supports,
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_support_send');

        $this->validate($request, [
            'title' => 'required|min:2',
            // 'department_id' => 'nullable|exists:support_departments,id',
            'user_id' => 'required|exists:users,id',
            'message' => 'required',
        ]);

        $data = $request->all();

        $support = Support::create([
            'user_id' => $data['user_id'],
            // 'department_id' => $data['department_id'] ?? 8,
            'title' => $data['title'],
            'status' => 'open',
            'webinar_id' => $data['webinar_id'] ?? null,
            'bundle_id' => $data['bundle_id'] ?? null,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        SupportConversation::create([
            'support_id' => $support->id,
            'supporter_id' => auth()->id(),
            'message' => $data['message'],
            'attach' => $data['attach'] ?? null,
            'created_at' => time(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tickect Created successfully'
        ], 201);
    }

    public function edit($id)
    {
        $this->authorize('admin_supports_reply');

        $support = Support::where('id', $id)
            ->whereNotNull('department_id')
            ->first();

        if (empty($support)) {
            abort(404);
        }

        $departments = SupportDepartment::all();

        $data = [
            'pageTitle' => trans('admin/main.edit_support_ticket_title'),
            'departments' => $departments,
            'support' => $support,
        ];

        return response()->json($data);
    }

    public function update($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_supports_reply');

        $data = $request->all();

        if (!empty($data['status'])) {
            $enumStatus = $this->getStatusEnumFromTranslation($data['status']);
            if (!$enumStatus) {
                return response()->json([
                    'message' => 'Invalid status translation.',
                    'provided' => $data['status'],
                ], 422);
            }
            $request->merge(['status' => $enumStatus]);
        }

        $this->validate($request, [
            'title' => 'sometimes|min:2',
            'user_id' => 'sometimes|exists:users,id',
            'message' => 'sometimes',
            'status' => 'sometimes|in:open,close,replied,supporter_replied',
        ]);

        $support = Support::findOrFail($id);

        $support->update([
            'user_id' => $request->input('user_id', $support->user_id),
            'title' => $request->input('title', $support->title),
            'status' => $request->input('status', $support->status),
            'updated_at' => time(),
        ]);

        return response()->json([
            'status' => 'success',
            'msg' => 'Ticket Updated Successfully',
            'support' => [
                'id' => $support->id,
                'title' => $support->title,
                'status' => $support->status,
                'translated_status' => trans('panel.' . $support->status),
            ]
        ]);
    }

    private function getStatusEnumFromTranslation($status)
    {
        $map = [
            'open' => 'open',
            'مفتوح' => 'open',

            'close' => 'close',
            'مغلق' => 'close',

            'replied' => 'replied',
            'تم الرد' => 'replied',

            'supporter_replied' => 'supporter_replied',
            'تم الرد من الدعم' => 'supporter_replied',
        ];

        return $map[$status] ?? null;
    }


    public function delete($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $this->authorize('admin_supports_delete');

        $support = Support::where('id', $id)->first();

        if (empty($support)) {
            abort(404);
        }

        $support->delete();

        return response()->json([
            'status' => 'success',
            'msg' => 'Ticket Deleted Successfully'
        ]);
    }

    public function conversationClose($id)
    {
        $this->authorize('admin_supports_reply');

        $support = Support::where('id', $id)
            ->first();

        if (empty($support)) {
            abort(404);
        }

        $support->update([
            'status' => 'close',
            'updated_at' => time()
        ]);

        return redirect(getAdminPanelUrl() . '/supports/' . $support->id . '/conversation');
    }

    public function conversation($id)
    {
        $this->authorize('admin_supports_reply');

        $support = Support::where('id', $id)
            ->where(function ($query) {
                $query->whereNotNull('department_id')
                    ->orWhereNotNull('webinar_id');
            })
            ->with(['department', 'conversations' => function ($query) {
                $query->with(['sender' => function ($qu) {
                    $qu->select('id', 'full_name', 'avatar');
                }, 'supporter' => function ($qu) {
                    $qu->select('id', 'full_name', 'avatar');
                }]);
                $query->orderBy('created_at', 'asc');
            }, 'user' => function ($qu) {
                $qu->select('id', 'full_name', 'role_name');
            }, 'webinar' => function ($qu) {
                $qu->with(['teacher' => function ($qu) {
                    $qu->select('id', 'full_name');
                }]);
            }])->first();

        if (empty($support)) {
            abort(404);
        }

        $data = [
            'pageTitle' => trans('admin/pages/users.support_conversation'),
            'support' => $support
        ];

        return view('admin.supports.conversation', $data);
    }

    public function storeConversation(Request $request, $id)
    {
        $this->authorize('admin_supports_reply');

        $this->validate($request, [
            'message' => 'required|string|min:2',
        ]);

        $data = $request->all();
        $support = Support::where('id', $id)
            //->whereNotNull('department_id')
            ->first();

        if (empty($support)) {
            abort(404);
        }

        $support->update([
            'status' => 'supporter_replied',
            'updated_at' => time()
        ]);

        SupportConversation::create([
            'support_id' => $support->id,
            'supporter_id' => auth()->id(),
            'message' => $data['message'],
            'attach' => $data['attach'],
            'created_at' => time(),
        ]);

        return redirect(getAdminPanelUrl() . '/supports/' . $support->id . '/conversation');
    }
}
