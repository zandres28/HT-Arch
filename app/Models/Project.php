<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    public const STATUSES = [
        'active' => 'Activo',
        'finished' => 'Finalizado',
        'paused' => 'Pausado',
        'cancelled' => 'Cancelado',
    ];

    protected $fillable = [
        'user_id', 'name', 'description', 'client', 'start_date', 'end_date',
        'status', 'color', 'notes', 'archived_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'archived_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class);
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function totalHours(): float
    {
        return (float) $this->workLogs()->sum('hours');
    }

    /** Proyectos disponibles para registrar horas (activos y no archivados). */
    public function scopeLoggable($query)
    {
        return $query->whereNull('archived_at')->where('status', 'active');
    }

    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }
}
