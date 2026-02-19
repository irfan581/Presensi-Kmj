<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

trait UploadGambar
{
    /**
     * FULL COMPRESS LOGIC
     * Mengubah foto ukuran besar (10MB+) menjadi sangat kecil (KB)
     */
    public function uploadCompressed($file, $path)
    {
        try {
            if (!$file || !$file->isValid()) {
                return null;
            }

            // 1. Nama file unik dengan ekstensi .jpg (Format paling enteng)
            $filename = Str::uuid() . '.jpg';
            $fullPath = $path . '/' . $filename;

            // 2. Inisialisasi Manager (Intervention V3)
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);

            // 3. FULL COMPRESSION STEPS:
            // - Resize ke 600px (Sangat ringan, tapi detail masih oke)
            // - Turunkan kualitas ke 50 (Full geprek)
            $image->scale(width: 600); 
            $encoded = $image->toJpeg(quality: 50); 

            // 4. Simpan ke Storage Public
            Storage::disk('public')->put($fullPath, (string) $encoded);

            Log::info("Full Compress Berhasil: $fullPath");

            return $fullPath;

        } catch (\Exception $e) {
            Log::error('Gagal Kompres, simpan manual: ' . $e->getMessage());
            // Jika mesin kompres error, terpaksa simpan asli agar aplikasi tidak Force Close
            return $file->store($path, 'public');
        }
    }
}