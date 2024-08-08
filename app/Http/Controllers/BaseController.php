<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];
  
        return response()->json($response, 200);
    }


    public function sendError($error, $errorMessages = [], $code = 422)
    {
        $response = [
            'success' => false,
            'errors' => $error,
        ];
  
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
  
        return response()->json($response, $code);
    }
}
