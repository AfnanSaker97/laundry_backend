<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderType;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Validator;
use Auth;
class OrderTypeController extends BaseController
{
    
    public function index()
    {
  
        $OrderType = Cache::remember('OrderType', 60, function () {
            return OrderType::all();
        });
        return $this->sendResponse($OrderType,'OrderType fetched successfully.');
  
    }
}
