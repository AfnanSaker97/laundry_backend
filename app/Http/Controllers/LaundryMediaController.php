<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaundryMedia;
use Validator;
use Auth;
use App\Http\Controllers\BaseController as BaseController;
class LaundryMediaController extends BaseController
{
    public function store(Request $request)
    {
        try{

        $validator = Validator::make($request->all(), [
            'laundry_id' => 'required|exists:laundries,id',
            'url_media' => 'required|array',
            'url_media.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validate each image
       ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }
    $laundryMedia = []; 

    foreach ($request->file('url_media') as $image) {

        $imageName = time() . '_' . uniqid() . '.' . $image->extension();
        // Move the file to the specified directory
        $image->move(public_path('Laundry'), $imageName);
        // Create the URL for the uploaded image
        $url = url('Laundry/' . $imageName);

        // Store media related to the advertisement
        $laundryMedia[] = LaundryMedia::create([
            'laundry_id' => $request->laundry_id,
            'url_image' => $url,
        ]);
    }

        return $this->sendResponse($laundryMedia,'Laundry Media added successfully');
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
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'laundry_id' => 'required|exists:laundries,id', ]);

        // Handle validation failures
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the advertisement media entry
        $laundryMedia = LaundryMedia::find($request->laundry_id);

        // Delete the entry
        $laundryMedia->delete();

        return $this->sendResponse($laundryMedia,'laundry Media deleted successfully');
    
    } catch (\Throwable $th) {
        // Handle any unexpected errors
        return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
        ], 500);
    }
}

}
