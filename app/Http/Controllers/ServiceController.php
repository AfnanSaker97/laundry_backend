<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
class ServiceController extends BaseController
{
    

    public function index()
    {
        try{
        $Services =  Service::all();
    
    
        return $this->sendResponse($Services, 'Services fetched successfully.');
    } catch (\Exception $e) {
        // Log error and return empty array
        return response()->json(['error' =>  $e->getMessage()], 500);
      
    }
    }

}
