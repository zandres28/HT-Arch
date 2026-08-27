<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\WorkLog;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function __construct(private FileStorageService $files) {}

    public function store(Request $request, WorkLog $workLog)
    {
        $maxKb = (int) env('MAX_UPLOAD_MB', 15) * 1024;

        $request->validate([
            'files' => ['required', 'array', 'max:10'],
            'files.*' => [
                'file',
                "max:{$maxKb}",
                'mimes:' . implode(',', Attachment::ALLOWED_EXTENSIONS),
            ],
        ], [
            'files.required' => 'Selecciona al menos un archivo.',
            'files.*.max' => 'Cada archivo puede pesar máximo ' . (int) env('MAX_UPLOAD_MB', 15) . ' MB.',
            'files.*.mimes' => 'Tipo de archivo no permitido.',
        ]);

        $count = 0;
        foreach ((array) $request->file('files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $workLog->attachments()->create($this->files->store($file, 'attachments'));
            $count++;
        }

        return back()->with('success', $count . ' ' . ($count === 1 ? 'evidencia adjuntada' : 'evidencias adjuntadas') . '.');
    }

    public function view(Attachment $attachment)
    {
        return $this->serve($attachment, false);
    }

    public function download(Attachment $attachment)
    {
        return $this->serve($attachment, true);
    }

    private function serve(Attachment $attachment, bool $download)
    {
        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'El archivo ya no existe en el servidor.');
        }

        // Cabeceras seguras: nunca ejecutar contenido subido.
        $disposition = $download ? 'attachment' : 'inline';
        $safeName = $this->asciiName($attachment->original_name);

        return response()->file(
            Storage::disk('local')->path($attachment->file_path),
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => "$disposition; filename=\"$safeName\"; filename*=UTF-8''" . rawurlencode($safeName),
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function destroy(Attachment $attachment)
    {
        $attachment->deleteFile();
        $attachment->delete();

        return back()->with('success', 'Evidencia eliminada.');
    }

    private function asciiName(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9_.-]/', '_', $name) ?: 'archivo';
    }
}
