<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdvertisementMedia;
use Carbon\Carbon;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Validator;
use Auth;
class AdvertisementMediaController extends BaseController
{
    

    public function store(Request $request)
    {
        try{

        $validator = Validator::make($request->all(), [
            'advertisement_id' => 'required|exists:advertisements,id',
            'url_media' => 'required|array', // Ensure url_media is an array for multiple files
            'url_media.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validate each image
       ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }
    $advertisementMedia = []; // Array to hold the created advertisement media records

    // Iterate through each image in the url_media array
    foreach ($request->file('url_media') as $image) {
        // Create a unique file name for each image
        $imageName = time() . '_' . uniqid() . '.' . $image->extension();
        // Move the file to the specified directory
        $image->move(public_path('Advertisement'), $imageName);
        // Create the URL for the uploaded image
        $url = url('Advertisement/' . $imageName);

        // Store media related to the advertisement
        $advertisementMedia[] = AdvertisementMedia::create([
            'advertisement_id' => $request->advertisement_id,
            'url_image' => $url,
        ]);
    }

        return $this->sendResponse($advertisementMedia,'Advertisement Media added successfully');
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
            'advertisement_media_id' => 'required|exists:advertisement_media,id', // Ensure the ID exists
        ]);

        // Handle validation failures
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the advertisement media entry
        $advertisementMedia = AdvertisementMedia::find($request->advertisement_media_id);

        // Delete the entry
        $advertisementMedia->delete();

        return $this->sendResponse($advertisementMedia,'Advertisement Media deleted successfully');
    
    } catch (\Throwable $th) {
        // Handle any unexpected errors
        return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
        ], 500);
    }
}

}
