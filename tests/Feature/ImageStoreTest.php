<?php

namespace Tests\Feature;

use App\Support\ImageStore;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageStoreTest extends TestCase
{
    public function test_uploaded_image_is_stored_as_webp(): void
    {
        Storage::fake('public');

        $source = tempnam(sys_get_temp_dir(), 'image-test-');
        $this->assertNotFalse($source);

        file_put_contents($source, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        try {
            $path = ImageStore::storePath($source, 'images');
        } finally {
            @unlink($source);
        }

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);
    }
}
