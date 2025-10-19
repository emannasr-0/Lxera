<?php

namespace App\Http\Controllers\Admin;

use App\Models\Webinar;
use App\Models\Prerequisite;
use Illuminate\Http\Request;
use App\Models\PrerequisiteWebinar;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class PrerequisiteController extends Controller
{
   public function store(Request $request)
{
    $this->authorize('admin_webinars_edit');

    $data = $request->all();

    $validator = Validator::make($data, [
        'webinar_id' => 'required',
        'prerequisite_id' => [
            'required',
            Rule::unique('prerequisites', 'prerequisite_id')
                ->where('webinar_id', $data['webinar_id']),
        ],
    ]);

    // ✅ تحقق مخصص: لا يمكن أن تكون الدورة شرطًا لنفسها
    $validator->after(function ($validator) use ($data) {
        if (!empty($data['webinar_id']) && $data['webinar_id'] == $data['prerequisite_id']) {
            $validator->errors()->add('prerequisite_id', 'A webinar cannot be a prerequisite of itself.');
        }
    });

    if ($validator->fails()) {
        return response([
            'code' => 422,
            'errors' => $validator->errors(),
        ], 422);
    }

    $required = (!empty($data['required']) && $data['required'] == 'on') ? true : false;

    $prerequisite = Prerequisite::create([
        'webinar_id' => $data['webinar_id'],
        'prerequisite_id' => $data['prerequisite_id'],
        'required' => $required,
        'created_at' => time(),
    ]);

    return response()->json([
        'code' => 200,
        'data' => $prerequisite
    ], 200);
}


    public function edit(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        if (!empty($request->get('item_id'))) {
            $Prerequisite = Prerequisite::select('required', 'webinar_id', 'prerequisite_id')
                ->where('id', $id)
                ->first();

            if (!empty($Prerequisite)) {
                $Prerequisite->webinar_title = $Prerequisite->prerequisiteWebinar->title;

                return response()->json([
                    'prerequisite' => $Prerequisite
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $data = $request->all();

        $this->validate($request, [
            'webinar_id' => 'required',
            'prerequisite_id' => 'required|unique:prerequisites,prerequisite_id,' . $id . ',id,webinar_id,' . $data['webinar_id'],
        ]);

        $required = (!empty($data['required']) and $data['required'] == 'on') ? true : false;

        $prerequisite = Prerequisite::find($id);
        $prerequisite = Prerequisite::find($id);

        if (!$prerequisite) {
            return response()->json([
                'code' => 404,
                'message' => 'Prerequisite not found.',
            ], 404);
        }

        $prerequisite =    $prerequisite->update([
            'webinar_id' => $data['webinar_id'],
            'prerequisite_id' => $data['prerequisite_id'],
            'required' => $required,
            'updated_at' => time()
        ]);

        return response()->json([
            'code' => 200,
            'data' => $prerequisite
        ], 200);
    }


    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        Prerequisite::find($id)->delete();

        return redirect()->back();
    }
    public function updatePrerequisite($url_name, Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $data = $request->all();

        $this->validate($request, [
            'webinar_id' => 'required',
            'prerequisite_id' => 'required|unique:prerequisites,prerequisite_id,' . $id . ',id,webinar_id,' . $data['webinar_id'],
        ]);

        $required = (!empty($data['required']) and $data['required'] == 'on') ? true : false;

        $prerequisite = Prerequisite::find($id);
        $prerequisite = Prerequisite::find($id);

        if (!$prerequisite) {
            return response()->json([
                'code' => 404,
                'message' => 'Prerequisite not found.',
            ], 404);
        }

        $prerequisite->update([
            'webinar_id' => $data['webinar_id'],
            'prerequisite_id' => $data['prerequisite_id'],
            'required' => $required,
            'updated_at' => time()
        ]);

        return response()->json([
            'code' => 200,
        ], 200);
    }
    public function destroyPrerequisite($url_name, Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $prerequisite =  Prerequisite::find($id)->delete();
        if (!$prerequisite) {
            return response()->json([
                'code' => 404,
                'message' => 'Prerequisite not found.',
            ], 404);
        }
        return response()->json([
            'code' => 200,
        ], 200);
    }
    public function list($url_name, Request $request, $id)
    {
        $webinar = Webinar::with([
            'prerequisites' => function ($query) {
                $query->where('required', true)
                    ->with([
                        'prerequisiteWebinar.teacher'
                    ]);
            }
        ])->find($id);

        if (!$webinar) {
            return response()->json([
                'code' => 404,
                'message' => 'Webinar not found.'
            ], 404);
        }

        $data = [];

        foreach ($webinar->prerequisites as $prerequisite) {
            $prerequisiteWebinar = $prerequisite->prerequisiteWebinar;

            if (!$prerequisiteWebinar) {
                continue;
            }

            $data[] = [
                'id' => $prerequisite->id,
                'webinar_id' => $webinar->id,
                'title' => $prerequisiteWebinar->title,
                'instructor' => $prerequisiteWebinar->teacher ? $prerequisiteWebinar->teacher->full_name : null,
                'price' => handlePrice($prerequisiteWebinar->price),
                'raw_price' => $prerequisiteWebinar->price,
                'publish_date' => dateTimeFormat($prerequisiteWebinar->created_at, 'j F Y | H:i'),
                'required' => $prerequisite->required ? trans('public.yes') : trans('public.no'),
                'is_required' => $prerequisite->required,
            ];
        }

        return response()->json([
            'code' => 200,
            'webinar_id' => $webinar->id,
            'prerequisites' => $data,
        ]);
    }
}
