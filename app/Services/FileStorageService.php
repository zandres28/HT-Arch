<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Almacena archivos subidos de forma segura en el disco local (storage/app),
 * fuera del webroot público. Los archivos se sirven únicamente a través de
 * rutas controladas de la aplicación.
 */
class FileStorageService
{
    /**
     * Guarda un archivo y devuelve los metadatos para persistir en BD.
     *
     * @return array{file_path: string, original_name: string, mime_type: string, extension: string, size_bytes: int}
     */
    public function store(UploadedFile $file, string $directory): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $safeName = $this->sanitizeFileName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = $safeName . '_' . Str::random(10) . '.' . $extension;

        $path = $file->storeAs($directory, $fileName, 'local');

        return [
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'size_bytes' => $file->getSize(),
        ];
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * Sanitiza el nombre base: solo letras, números, guiones y guiones bajos.
     */
    private function sanitizeFileName(string $name): string
    {
        $name = Str::ascii($name);
        $name = preg_replace('/[^A-Za-z0-9_\-]+/', '-', $name);
        $name = trim($name, '-_');

        return $name === '' ? 'archivo' : Str::limit($name, 60, '');
    }
}
