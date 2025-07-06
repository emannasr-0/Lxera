<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\Models\Api\SupportsQuestion;
use Illuminate\Http\Request;

class SupportsQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $supports = SupportsQuestion::all();
        return response()->json([
            'status' => 'success',
            'data' => $supports
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
        $data = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'status' => 'in:open,closed'
        ]);
        $data['created_at'] = time();
        $question = SupportsQuestion::create($data);

        return response()->json([
            'status' => 'success',
            'msg' => 'Question Created successfully',
            'data' => $question
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $question = SupportsQuestion::find($id);

        if (!$question) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $question
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $question = SupportsQuestion::find($id);

        if (!$question) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->validate([
            'question' => 'sometimes|required|string',
            'answer' => 'sometimes|required|string',
            'status' => 'sometimes|in:open,closed',
        ]);

        $question->update($data);

        return response()->json([
            'status' => 'success',
            'msg' => 'Ticket Updated Successfully',
            'data' => $question
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $question = SupportsQuestion::find($id);

        if (!$question) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $question->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
