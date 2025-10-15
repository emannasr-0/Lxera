<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use Illuminate\Http\Request;
use App\Models\Code;
use Illuminate\Support\Facades\Validator;

class CodesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('admin_codes_list');
        removeContentLocale();

        $codes = Code::all();

        return response()->json([
            'success' => true,
            'message' => $codes
        ]);
    }

    public function index_instructor()
    {
        $this->authorize('instructor_codes_list');
        removeContentLocale();

        $codes = Code::all();

        return response()->json([
            'success' => true,
            'message' => $codes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('admin_codes_create');

        $validated = $request->validate([
            'student_code' => 'required|string',
        ]);

        $studentCode = $validated['student_code'];

        $lastCode = Code::latest()->first();

        if ($lastCode) {
            $lastCode->update([
                'student_code' => $studentCode,
            ]);
        } else {
            Code::create([
                'student_code' => $studentCode,
            ]);
        }

        return response()->json(['success' => true]);
    }



    public function store_instructor(Request $request)
    {
        $this->authorize('instructor_codes_create');

        $validated = $request->validate([
            'instructor_code' => 'required|string',
        ]);

        $instructorCode = $validated['instructor_code'];
        $lastCode = Code::latest()->first();

        if ($lastCode) {
            $lastCode->update([
                'instructor_code' => $instructorCode,
            ]);
        } else {
            Code::create([
                'instructor_code' => $instructorCode
            ]);
        }

        return response()->json(['success' => true]);
    }
}
