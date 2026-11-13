<?php

namespace App\Http\Controllers;

use App\Support\ImageStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        // Лимит согласован с клиентом (TipTapEditor сжимает до 2.5 МБ, но проверяет
        // исходник по 20 МБ и после сжатия по 10 МБ): сюда доходят только ужатые файлы.
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $path = ImageStore::storeUploaded($request->file('image'), 'images');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }
}
