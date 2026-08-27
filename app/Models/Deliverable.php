<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Deliverable extends Model
{
    public const TYPES = [
        'document' => 'Documento',
        'spreadsheet' => 'Hoja de cálculo',
        'code' => 'Código fuente',
        'application' => 'Aplicación',
        'report' => 'Informe',
        'presentation' => 'Presentación',
        'design' => 'Diseño',
        'pdf' => 'PDF',
        'archive' => 'Archivo comprimido',
        'other' => 'Otro',
    ];

    public const ALLOWED_EXTENSIONS = [
        'pdf', 'txt', 'md',
        'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx',
        'zip', 'rar', '7z', 'tar', 'gz',
        'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg',
        'sql', 'json', 'xml', 'html', 'css', 'js', 'php', 'py',
    ];

    protected $fillable = [
        'project_id', 'work_log_id', 'name', 'description', 'type', 'version',
        'file_path', 'original_name', 'mime_type', 'extension', 'size_bytes',
        'date', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function workLog(): BelongsTo
    {
        return $this->belongsTo(WorkLog::class);
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size_bytes;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes . ' B';
    }

    public function deleteFile(): void
    {
        if ($this->file_path && Storage::disk('local')->exists($this->file_path)) {
            Storage::disk('local')->delete($this->file_path);
        }
    }

    protected static function booted(): void
    {
        static::deleting(function (Deliverable $deliverable) {
            $deliverable->deleteFile();
        });
    }
}
