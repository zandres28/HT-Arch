<?php

namespace App\Http\Controllers;

use App\Models\Deliverable;
use App\Models\Project;
use App\Models\WorkLog;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeliverableController extends Controller
{
    public function __construct(private FileStorageService $files) {}

    public function create(Request $request)
    {
        $project = $request->integer('project_id') ? Project::find($request->integer('project_id')) : null;
        $workLog = $request->integer('work_log_id') ? WorkLog::find($request->integer('work_log_id')) : null;

        if (! $project && $workLog) {
            $project = $workLog->project;
        }

        $deliverable = new Deliverable([
            'project_id' => $project?->id,
            'work_log_id' => $workLog?->id,
            'date' => today(),
        ]);

        return view('deliverables.create', [
            'deliverable' => $deliverable,
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'types' => Deliverable::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $deliverable = new Deliverable($data);
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $deliverable->fill($this->files->store($request->file('file'), 'deliverables'));
        }
        $deliverable->save();

        $redirect = $deliverable->work_log_id
            ? route('work-logs.show', $deliverable->work_log_id)
            : route('projects.show', $deliverable->project_id);

        return redirect($redirect)
            ->with('success', 'Entregable guardado correctamente.');
    }

    public function edit(Deliverable $deliverable)
    {
        return view('deliverables.edit', [
            'deliverable' => $deliverable,
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'types' => Deliverable::TYPES,
        ]);
    }

    public function update(Request $request, Deliverable $deliverable)
    {
        $data = $this->validated($request);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $deliverable->deleteFile();
            $deliverable->fill($this->files->store($request->file('file'), 'deliverables'));
        }
        $deliverable->fill($data);
        $deliverable->save();

        $redirect = $deliverable->work_log_id
            ? route('work-logs.show', $deliverable->work_log_id)
            : route('projects.show', $deliverable->project_id);

        return redirect($redirect)
            ->with('success', 'Entregable actualizado correctamente.');
    }

    public function destroy(Deliverable $deliverable)
    {
        $redirect = $deliverable->work_log_id
            ? route('work-logs.show', $deliverable->work_log_id)
            : route('projects.show', $deliverable->project_id);

        $deliverable->deleteFile();
        $deliverable->delete();

        return redirect($redirect)->with('success', 'Entregable eliminado.');
    }

    public function download(Deliverable $deliverable)
    {
        if (! $deliverable->file_path || ! \Illuminate\Support\Facades\Storage::disk('local')->exists($deliverable->file_path)) {
            abort(404, 'El archivo ya no existe en el servidor.');
        }

        $safeName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $deliverable->original_name) ?: 'archivo';

        return response()->download(
            \Illuminate\Support\Facades\Storage::disk('local')->path($deliverable->file_path),
            $safeName
        );
    }

    private function validated(Request $request): array
    {
        $maxKb = (int) env('MAX_UPLOAD_MB', 15) * 1024;

        $data = $request->validate([
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'work_log_id' => ['nullable', 'integer', Rule::exists('work_logs', 'id')],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::in(array_keys(Deliverable::TYPES))],
            'version' => ['nullable', 'string', 'max:30'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'file' => [
                'nullable', 'file', "max:{$maxKb}",
                'mimes:' . implode(',', Deliverable::ALLOWED_EXTENSIONS),
            ],
        ], [
            'project_id.required' => 'Selecciona el proyecto al que pertenece el entregable.',
            'name.required' => 'Indica el nombre del entregable.',
            'type.required' => 'Selecciona el tipo de entregable.',
            'date.required' => 'La fecha es obligatoria.',
            'file.mimes' => 'Tipo de archivo no permitido.',
            'file.max' => 'El archivo puede pesar máximo ' . (int) env('MAX_UPLOAD_MB', 15) . ' MB.',
        ]);

        unset($data['file']);

        return $data;
    }
}
