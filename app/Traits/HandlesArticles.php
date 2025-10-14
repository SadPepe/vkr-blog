<?php 

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Article;

trait HandlesArticles
{
    protected function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (Article::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    protected function deleteImage($path)
    {
        if (str_starts_with($path, '/storage/')) {
            $relativePath = str_replace('/storage/', '', $path);
            Storage::disk('public')->delete($relativePath);
        }
    }
}