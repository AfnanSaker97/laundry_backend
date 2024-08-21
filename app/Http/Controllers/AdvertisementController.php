<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Advertisement;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Validator;
use Auth;
class AdvertisementController extends BaseController
{
    
    public function index()
    {
        try {
        $Advertisement = Cache::remember('advertisement', 60, function () {
            return Advertisement::all();
        });
        return $this->sendResponse($Advertisement,'Advertisement fetched successfully.');
  
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => $th->getMessage()
        ], 500);
    }
  }
}
