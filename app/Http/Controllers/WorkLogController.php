<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\WorkLog;
use App\Services\FileStorageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkLogController extends Controller
{
    public function __construct(private FileStorageService $files) {}

    public function index(Request $request)
    {
        $query = WorkLog::query()
            ->with('project')
            ->withCount(['attachments', 'deliverables'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        // Filtros
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $week = $request->input('week');   // formato YYYY-Www
        $month = $request->input('month'); // formato YYYY-MM
        $day = $request->input('date');    // formato YYYY-MM-DD

        if ($day && preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            $query->whereDate('date', $day);
        } elseif ($week && preg_match('/^(\d{4})-W(\d{2})$/', $week, $m)) {
            $start = Carbon::now()->setISODate((int) $m[1], (int) $m[2])->startOfWeek();
            $query->whereBetween('date', [$start->toDateString(), $start->copy()->endOfWeek()->toDateString()]);
        } elseif ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $query->whereBetween('date', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()]);
        } else {
            if ($dateFrom) {
                $query->whereDate('date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('date', '<=', $dateTo);
            }
        }

        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($status = $request->input('project_status')) {
            $query->whereHas('project', fn ($q) => $q->where('status', $status));
        }

        $logs = $query->paginate(20)->withQueryString();
        $totalHours = (float) $query->toBase()->cloneWithout(['orders', 'limit', 'offset'])->sum('hours');

        return view('work-logs.index', [
            'logs' => $logs,
            'totalHours' => $totalHours,
            'projects' => Project::orderBy('name')->get(['id', 'name', 'color']),
            'statuses' => Project::STATUSES,
            'filters' => $request->only(['date_from', 'date_to', 'week', 'month', 'date', 'project_id', 'project_status']),
        ]);
    }

    public function create(Request $request)
    {
        $log = new WorkLog([
            'date' => today(),
            'project_id' => $request->integer('project_id') ?: null,
        ]);

        return view('work-logs.create', [
            'log' => $log,
            'projects' => $this->loggableProjects(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $data) {
            $log = WorkLog::create($data);
            $this->storeAttachments($request, $log);
        });

        return redirect()
            ->route('work-logs.index')
            ->with('success', 'Registro de horas creado correctamente.');
    }

    public function show(WorkLog $workLog)
    {
        $workLog->load(['project', 'attachments', 'deliverables']);

        return view('work-logs.show', ['log' => $workLog]);
    }

    public function edit(WorkLog $workLog)
    {
        return view('work-logs.edit', [
            'log' => $workLog,
            'projects' => $this->loggableProjects($workLog->project),
        ]);
    }

    public function update(Request $request, WorkLog $workLog)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $workLog, $data) {
            $workLog->update($data);
            $this->storeAttachments($request, $workLog);
        });

        return redirect()
            ->route('work-logs.show', $workLog)
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(WorkLog $workLog)
    {
        DB::transaction(function () use ($workLog) {
            foreach ($workLog->attachments as $attachment) {
                $attachment->deleteFile();
            }
            // Los entregables se conservan (work_log_id pasa a NULL por la FK).
            $workLog->delete();
        });

        return redirect()
            ->route('work-logs.index')
            ->with('success', 'Registro eliminado.');
    }

    public function duplicate(WorkLog $workLog)
    {
        $copy = $workLog->replicate(['created_at', 'updated_at']);
        $copy->date = today();
        $copy->push();

        return redirect()
            ->route('work-logs.edit', $copy)
            ->with('success', 'Registro duplicado con la fecha de hoy. Ajusta lo necesario y guarda.');
    }

    private function validated(Request $request): array
    {
        // Normalizar horas: acepta "3.5", "3,5" o "3:30".
        if ($request->filled('hours')) {
            $parsed = WorkLog::parseHours($request->input('hours'));
            $request->merge(['hours' => $parsed]);
        }

        $data = $request->validate([
            'project_id' => [
                'required', 'integer',
                Rule::exists('projects', 'id')->whereNull('archived_at'),
            ],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'activity' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'hours' => ['required', 'numeric', 'gt:0', 'max:24'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'files' => ['nullable', 'array', 'max:10'],
            'files.*' => [
                'file',
                'max:' . ((int) env('MAX_UPLOAD_MB', 15)) * 1024,
                'mimes:' . implode(',', Attachment::ALLOWED_EXTENSIONS),
            ],
        ], [
            'project_id.required' => 'Selecciona un proyecto.',
            'project_id.exists' => 'El proyecto seleccionado no existe o está archivado.',
            'date.required' => 'La fecha es obligatoria.',
            'date.before_or_equal' => 'No puedes registrar horas en una fecha futura.',
            'activity.required' => 'Describe la actividad realizada.',
            'hours.required' => 'Indica el número de horas trabajadas.',
            'hours.numeric' => 'Las horas deben ser un valor válido (p. ej. 3.5 o 3:30).',
            'hours.gt' => 'Las horas deben ser mayores que cero.',
            'hours.max' => 'Un registro no puede superar 24 horas.',
            'end_time.after' => 'La hora de finalización debe ser posterior a la de inicio.',
            'files.max' => 'Puedes adjuntar máximo 10 archivos a la vez.',
            'files.*.max' => 'Cada archivo puede pesar máximo ' . (int) env('MAX_UPLOAD_MB', 15) . ' MB.',
            'files.*.mimes' => 'Tipo de archivo no permitido. Extensiones válidas: ' . implode(', ', Attachment::ALLOWED_EXTENSIONS),
        ]);

        unset($data['files']);

        return $data;
    }

    private function storeAttachments(Request $request, WorkLog $log): void
    {
        foreach ((array) $request->file('files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $log->attachments()->create($this->files->store($file, 'attachments'));
        }
    }

    /** Proyectos seleccionables para registrar horas (incluye el actual si ya no es seleccionable). */
    private function loggableProjects(?Project $current = null)
    {
        $projects = Project::loggable()->orderBy('name')->get(['id', 'name', 'color']);

        if ($current && ! $projects->contains($current->id)) {
            $projects->push($current);
        }

        return $projects;
    }
}
