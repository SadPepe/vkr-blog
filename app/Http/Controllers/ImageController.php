<?php

namespace App\Http\Controllers;

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
    
}
