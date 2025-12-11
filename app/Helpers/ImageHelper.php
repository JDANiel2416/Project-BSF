<?php

namespace App\Helpers;

class ImageHelper {

    /**
     * Procesa, redimensiona, corrige orientación y convierte una imagen a WebP.
     * * @param string $source Ruta temporal del archivo
     * @param string $destinationPath Ruta completa de destino SIN extensión (ej: /var/www/public/uploads/foto)
     * @param int $quality Calidad WebP (0-100). 75 es ideal.
     * @param int $maxWidth Ancho máximo permitido (ej: 1280px).
     * @return string|false Retorna el nombre del archivo final con extensión o false.
     */
    public static function uploadAndCompress($source, $destinationPath, $quality = 75, $maxWidth = 1280) {
        $info = getimagesize($source);
        if (!$info) return false;

        $mime = $info['mime'];
        $image = null;

        // 1. Crear el recurso según el tipo
        switch ($mime) {
            case 'image/jpeg': 
                $image = imagecreatefromjpeg($source); 
                // Corregir rotación automática de móviles si existe EXIF
                if (function_exists('exif_read_data')) {
                    $exif = @exif_read_data($source);
                    if ($exif && isset($exif['Orientation'])) {
                        $image = self::fixOrientation($image, $exif['Orientation']);
                    }
                }
                break;
            case 'image/png': 
                $image = imagecreatefrompng($source); 
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/webp': 
                $image = imagecreatefromwebp($source); 
                break;
            default: return false;
        }

        if (!$image) return false;

        // 2. Redimensionar si es muy grande (Optimización crítica de peso)
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = floor($height * ($maxWidth / $width));
            
            $tempImg = imagecreatetruecolor($newWidth, $newHeight);
            
            // Mantener transparencia para PNG/WebP redimensionados
            if ($mime == 'image/png' || $mime == 'image/webp') {
                imagealphablending($tempImg, false);
                imagesavealpha($tempImg, true);
                $transparent = imagecolorallocatealpha($tempImg, 255, 255, 255, 127);
                imagefilledrectangle($tempImg, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($tempImg, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $tempImg;
        }

        // 3. Guardar como WebP
        $finalPath = $destinationPath . '.webp';
        $result = imagewebp($image, $finalPath, $quality);

        // 4. Limpiar memoria
        imagedestroy($image);

        return $result ? $finalPath : false;
    }

    private static function fixOrientation($image, $orientation) {
        switch ($orientation) {
            case 3: return imagerotate($image, 180, 0);
            case 6: return imagerotate($image, -90, 0);
            case 8: return imagerotate($image, 90, 0);
        }
        return $image;
    }
}