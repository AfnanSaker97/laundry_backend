<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
class AddressController extends BaseController
{
    public function store(Request $request)
    {
        try {
            $validator =Validator::make($request->all(), [
                'full_name'=> 'required',
                'address_line_1' => 'required',
                'address_line_2' => 'nullable',
                'city'=> 'required|string',
                'country' => 'nullable|string',
                'postcode' => 'required',
                'contact_number'=> 'required',
                'latitude' => 'required|between:-90,90',
                'longitude' => 'required|between:-180,180',

            ]); 
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all());       
            }
        
            $user = Auth::user();
            $address = Address::create([
                'full_name' => $request->full_name,
                'user_id'=> $user->id,
                'email' => $user->email,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2?? '0',
                'city' => $request->city,
                'country' => $request->country?? '0',
                'postcode' => $request->postcode,
                'contact_number' => $request->contact_number,
                'postcode' => $request->postcode,
                'contact_number' => $request->contact_number,
           ]);
            
        return $this->sendResponse($address,'Address created successfully.');
    
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 


    }
}
