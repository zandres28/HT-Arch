<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkLog extends Model
{
    protected $fillable = [
        'user_id', 'project_id', 'date', 'activity', 'description',
        'hours', 'start_time', 'end_time', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::deleting(function (WorkLog $log) {
            $log->attachments()->get()->each->delete();
            $log->deliverables()->get()->each->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class);
    }

    public function imageAttachments()
    {
        return $this->attachments->filter->isImage();
    }

    public function otherAttachments()
    {
        return $this->attachments->reject->isImage();
    }

    /**
     * Normaliza una entrada de horas ("3.5", "3,5" o "3:30") a decimal.
     * Devuelve null si el formato es inválido.
     */
    public static function parseHours(?string $input): ?float
    {
        if ($input === null) {
            return null;
        }
        $input = trim(str_replace(',', '.', $input));
        if ($input === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):([0-5]?\d)$/', $input, $m)) {
            return round((int) $m[1] + ((int) $m[2]) / 60, 2);
        }

        if (is_numeric($input)) {
            return round((float) $input, 2);
        }

        return null;
    }
}
