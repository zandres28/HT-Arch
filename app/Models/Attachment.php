<?php

namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    /** Extensiones permitidas para evidencias. */
    public const ALLOWED_EXTENSIONS = [
        'png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp',
        'pdf', 'txt', 'md', 'log',
        'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx',
        'zip', 'rar', '7z',
    ];

    public const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'];

    protected $fillable = [
        'work_log_id', 'file_path', 'original_name', 'mime_type', 'extension', 'size_bytes',
    ];

    public function workLog(): BelongsTo
    {
        return $this->belongsTo(WorkLog::class);
    }

    public function isImage(): bool
    {
        return in_array(strtolower($this->extension), self::IMAGE_EXTENSIONS, true);
    }

    public function isPdf(): bool
    {
        return strtolower($this->extension) === 'pdf';
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
        static::deleting(function (Attachment $attachment) {
            $attachment->deleteFile();
        });
    }
}
