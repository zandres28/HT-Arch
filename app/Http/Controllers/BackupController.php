<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    public function __construct(private BackupService $backup) {}

    public function index()
    {
        $backupsDir = storage_path('app/backups');
        $existing = is_dir($backupsDir)
            ? collect(File::files($backupsDir))
                ->filter(fn ($f) => $f->getExtension() === 'zip')
                ->sortByDesc(fn ($f) => $f->getMTime())
                ->values()
            : collect();

        return view('backup.index', compact('existing'));
    }

    public function export()
    {
        try {
            $path = $this->backup->export();
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo generar el respaldo: ' . $e->getMessage());
        }

        return Response::download($path, basename($path))->deleteFileAfterSend(true);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup' => ['required', 'file', 'mimes:zip', 'max:102400'],
        ]);

        $path = $request->file('backup')->storeAs('backups', 'incoming_' . uniqid() . '.zip');
        $full = storage_path('app/' . $path);

        try {
            $this->backup->import($full);
            File::delete($full);
        } catch (\Throwable $e) {
            File::delete($full);

            return back()->with('error', 'No se pudo restaurar: ' . $e->getMessage());
        }

        return back()->with('success', 'Respaldo restaurado correctamente.');
    }
}
