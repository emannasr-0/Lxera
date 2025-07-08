<?php

namespace App\Http\Controllers\Api\Traits;


trait sendError
{

    function sendError($data = [], $message = '', $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
