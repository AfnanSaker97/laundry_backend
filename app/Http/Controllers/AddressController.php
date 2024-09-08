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
                'address' => 'required',
                'lat' => 'required',
                'lng' => 'required',
               
            ]); 
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all());       
            }
        
            $user = Auth::user();
          // Check if it's the first address
           $isFirstAddress = Address::where('user_id', $user->id)->count() == 0;
 
         
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
                'address' => $request->address,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'isActive' => $isFirstAddress ? 1 : 0,

           ]);
            
        return $this->sendResponse($address,'Address created successfully.');
    
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 


    }



    

    public function show(Request $request)
{
 
    try {
        $validator =Validator::make($request->all(), [
            'id' => 'required|exists:addresses',


        ]); 
       
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
      
        // Find the address by ID
        $address = Address::findOrFail($request->id);

    
        return $this->sendResponse($address,'Address fetched successfully.');

    
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 
}



public function addressUser(Request $request)
{
    try {
        $user = Auth::user();
        // Find the address by ID
        $address = Address::where($user->address_id)->get();

    
        return $this->sendResponse($address,'Address fetched successfully.');

    
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 
}



    public function destroy(Request $request)
{
  try {

        $validator =Validator::make($request->all(), [
            'id' => 'required|exists:addresses',


        ]); 
       
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());       
        }
      
        // Find the address by ID
        $address = Address::findOrFail($request->id);

        // Delete the address
        $address->delete();

        return $this->sendResponse($address,'Address deleted successfully.');

    
    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500); 
    
    } 
}




public function update(Request $request)
{
    try {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:addresses,id', // Ensure the address exists

        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->all());
        }

        // Find the address by ID
        $address = Address::findOrFail($request->id);

    // Update the address with the request data
    $address->update([
        'full_name' => $request->full_name ?? $address->full_name,
        'address_line_1' => $request->address_line_1 ?? $address->address_line_1,
        'address_line_2' => $request->address_line_2 ?? $address->address_line_2,
        'city' => $request->city ?? $address->city,
        'country' => $request->country ?? $address->country,
        'postcode' => $request->postcode ?? $address->postcode,
        'contact_number' => $request->contact_number ?? $address->contact_number,
        'address' => $request->address ?? $address->address,
    ]);

        return $this->sendResponse($address, 'Address updated successfully.');

    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => $th->getMessage()
        ], 500);
    }
}

}
