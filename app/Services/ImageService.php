<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImageService
{
    /**
     * Convierte y guarda una imagen subida en formato WebP.
     * Si el servidor no soporta WebP o la conversión falla, guarda la imagen en su formato original como fallback.
     *
     * @param UploadedFile $file Archivo subido
     * @param string $destinationDir Directorio destino absoluto (ej: public_path('storage/productos'))
     * @param string $filenameWithoutExt Nombre del archivo sin extensión (ej: el id del producto)
     * @param int $quality Calidad de compresión WebP (0 a 100, defecto 80)
     * @return string Nombre del archivo resultante con extensión (ej: '15.webp')
     */
    public static function convertToWebp(UploadedFile $file, string $destinationDir, string $filenameWithoutExt, int $quality = 80): string
    {
        if (!file_exists($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true, true);
        }

        // Si la extensión GD está disponible y soporta WebP
        if (extension_loaded('gd') && function_exists('imagewebp')) {
            try {
                $imageContent = file_get_contents($file->getRealPath());
                $image = @imagecreatefromstring($imageContent);

                if ($image !== false) {
                    $webpFilename = $filenameWithoutExt . '.webp';
                    $targetPath = rtrim($destinationDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $webpFilename;

                    // Preservar canal alfa / transparencia (para PNG y WebP con transparencia)
                    imagepalettetotruecolor($image);
                    imagealphablending($image, false);
                    imagesavealpha($image, true);

                    // Guardar como WebP
                    $success = imagewebp($image, $targetPath, $quality);
                    imagedestroy($image);

                    if ($success) {
                        return $webpFilename;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Fallo en la conversión a WebP: ' . $e->getMessage());
            }
        }

        // Fallback: guardar archivo con extensión original si GD no está disponible o falló
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $fallbackFilename = $filenameWithoutExt . '.' . $extension;
        $file->move($destinationDir, $fallbackFilename);

        return $fallbackFilename;
    }

    /**
     * Elimina una imagen existente del almacenamiento si existe.
     *
     * @param string|null $relativePath Ruta relativa (ej: 'storage/productos/1.webp')
     */
    public static function deleteImage(?string $relativePath): void
    {
        if (!empty($relativePath)) {
            $fullPath = public_path($relativePath);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }
    }
}
