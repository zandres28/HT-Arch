<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query()->withCount('workLogs')->withSum('workLogs', 'hours');

        if ($request->boolean('archived')) {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $projects = $query->orderBy('name')->paginate(12)->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'showArchived' => $request->boolean('archived'),
            'statusFilter' => $status,
            'statuses' => Project::STATUSES,
        ]);
    }

    public function create()
    {
        return view('projects.create', [
            'project' => new Project(['color' => '#6366f1', 'status' => 'active']),
            'statuses' => Project::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $project = Project::create($data);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Proyecto creado correctamente.');
    }

    public function show(Project $project)
    {
        $project->loadSum('workLogs', 'hours');

        $recentLogs = $project->workLogs()
            ->withCount(['attachments', 'deliverables'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10);

        $deliverables = $project->deliverables()
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        return view('projects.show', compact('project', 'recentLogs', 'deliverables'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', [
            'project' => $project,
            'statuses' => Project::STATUSES,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $data = $this->validated($request);

        $project->update($data);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Proyecto actualizado correctamente.');
    }

    public function destroy(Project $project)
    {
        if ($project->workLogs()->exists()) {
            return back()->with('error', 'No se puede eliminar un proyecto con registros de horas. Archívalo en su lugar.');
        }

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Proyecto eliminado.');
    }

    public function archive(Project $project)
    {
        $project->update(['archived_at' => now()]);

        return back()->with('success', "Proyecto «{$project->name}» archivado.");
    }

    public function unarchive(Project $project)
    {
        $project->update(['archived_at' => null]);

        return back()->with('success', "Proyecto «{$project->name}» restaurado.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'client' => ['nullable', 'string', 'max:150'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'name.required' => 'El nombre del proyecto es obligatorio.',
            'end_date.after_or_equal' => 'La fecha de finalización no puede ser anterior a la fecha de inicio.',
            'color.regex' => 'El color debe ser un valor hexadecimal válido (p. ej. #6366f1).',
        ]);
    }
}
