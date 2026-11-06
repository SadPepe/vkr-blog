<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;



class ImageController extends Controller
{
  

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048'
        ]);

        $path = $request->file('image')->store('images', 'public');
        
        return response()->json([
            'url' => Storage::url($path)
        ]);
    }
    
=======
use App\Support\ImageStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $path = ImageStore::storeUploaded($request->file('image'), 'images');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }
>>>>>>> my-saved-changes
}
