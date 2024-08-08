<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cache;
use App\Models\Laundry;
use App\Models\MySession;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Validator;
class LaundryController extends BaseController
{

 public function index()
    {
        
        $Laundries = Cache::remember('Laundries', 60, function () {
            return Laundry::all();
        });
        return $this->sendResponse($Laundries,'Laundries fetched successfully.');
    }
}
