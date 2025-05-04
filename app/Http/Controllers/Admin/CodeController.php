<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Code;
use Illuminate\Support\Facades\Validator;

class CodeController extends Controller
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
        
        return view('admin.codes.list',['codes' => $codes]);
    }
    
    public function index_instructor()
    {
        $this->authorize('instructor_codes_list');
         removeContentLocale();

        $codes = Code::all();
        
        return view('admin.codes.list_instructor',['codes' => $codes]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         $this->authorize('admin_codes_create');

        return view('admin.codes.create');
    }
    
     public function create_instructor()
    {
         $this->authorize('instructor_codes_create');

        return view('admin.codes.create_instructor');
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

        $data = $request['student_code'];
        $locale = $data['locale'] ?? getDefaultLocale();

        $rules = [
            'student_code' => 'required',
        ];
        $validate= $request->validate($rules);


        if (!$validate) {
            
            return redirect()->back();
        }
        else{
            $lastCode = Code::latest()->first();

            if ($lastCode) {
                $lastCode->update([
                    'student_code' => $data,
                ]);
            } else {
        
                $code = Code::create([
                    'student_code' => $data,
                ]);
            }
            
           return redirect()->route('codes');
        }

            
      
    }
    
    
    public function store_instructor(Request $request)
    {
        $this->authorize('instructor_codes_create');

        $data = $request['instructor_code'];
        $locale = $data['locale'] ?? getDefaultLocale();

        $rules = [
            'instructor_code' => 'required',
        ];
        $validate= $request->validate($rules);


        if (!$validate) {
            
            return redirect()->back();
        }
        else{
            $lastCode = Code::latest()->first();

            if ($lastCode) {
                $lastCode->update([
                    'instructor_code' => $data,
                ]);
            } else {
        
                $code = Code::create([
                    'instructor_code' => $data,
                ]);
            }
            
           return redirect()->route('instructor_codes');
        }

            
      
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
