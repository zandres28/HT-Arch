@csrf

<div x-data="workLogForm()" class="space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="date" class="block text-sm font-medium text-slate-700 mb-1">Fecha <span class="text-red-500">*</span></label>
            <input type="date" name="date" id="date" required max="{{ today()->format('Y-m-d') }}"
                   value="{{ old('date', $log->date?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </div>

        <div>
            <label for="project_id" class="block text-sm font-medium text-slate-700 mb-1">Proyecto <span class="text-red-500">*</span></label>
            <select name="project_id" id="project_id" required
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">Selecciona un proyecto…</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) old('project_id', $log->project_id) === (string) $project->id)>
                        {{ $project->name }}{{ $project->isArchived() ? ' (archivado)' : '' }}
                    </option>
                @endforeach
            </select>
            @if ($projects->isEmpty())
                <p class="mt-1 text-xs text-amber-600">No hay proyectos activos. <a href="{{ route('projects.create') }}" class="underline">Crea uno primero</a>.</p>
            @endif
        </div>
    </div>

    <div>
        <label for="activity" class="block text-sm font-medium text-slate-700 mb-1">Actividad <span class="text-red-500">*</span></label>
        <input type="text" name="activity" id="activity" required maxlength="200"
               value="{{ old('activity', $log->activity) }}"
               placeholder="p. ej. Desarrollo del módulo de clientes"
               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
        <textarea name="description" id="description" rows="2" maxlength="5000"
                  placeholder="Detalle del trabajo realizado (opcional)"
                  class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $log->description) }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div>
            <label for="hours" class="block text-sm font-medium text-slate-700 mb-1">Horas trabajadas <span class="text-red-500">*</span></label>
            <input type="text" name="hours" id="hours" required inputmode="decimal"
                   x-model="hours" @change="hoursManuallyEdited = true"
                   value="{{ old('hours', $log->hours ? number_format((float) $log->hours, 2, '.', '') : '') }}"
                   placeholder="p. ej. 3.5 o 3:30"
                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <p class="mt-1 text-xs text-slate-400">Decimales (3.5) u horas:minutos (3:30).</p>
        </div>

        <div>
            <label for="start_time" class="block text-sm font-medium text-slate-700 mb-1">Hora de inicio <span class="text-slate-400 font-normal">(opcional)</span></label>
            <input type="time" name="start_time" id="start_time" x-model="startTime" @change="calcHours()"
                   value="{{ old('start_time', $log->start_time ? substr($log->start_time, 0, 5) : '') }}"
                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </div>

        <div>
            <label for="end_time" class="block text-sm font-medium text-slate-700 mb-1">Hora de finalización <span class="text-slate-400 font-normal">(opcional)</span></label>
            <input type="time" name="end_time" id="end_time" x-model="endTime" @change="calcHours()"
                   value="{{ old('end_time', $log->end_time ? substr($log->end_time, 0, 5) : '') }}"
                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </div>
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Observaciones</label>
        <textarea name="notes" id="notes" rows="2" maxlength="2000"
                  class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('notes', $log->notes) }}</textarea>
    </div>

    <div>
        <label for="files" class="block text-sm font-medium text-slate-700 mb-1">Evidencias <span class="text-slate-400 font-normal">(opcional, máx. 10 archivos de {{ (int) env('MAX_UPLOAD_MB', 15) }} MB)</span></label>
        <input type="file" name="files[]" id="files" multiple
               accept=".png,.jpg,.jpeg,.gif,.webp,.bmp,.pdf,.txt,.md,.log,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.zip,.rar,.7z"
               class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        <p class="mt-1 text-xs text-slate-400">Imágenes, PDF, documentos o comprimidos. Podrás añadir más al editar el registro.</p>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg shadow-sm transition-colors">
            Guardar registro
        </button>
        <a href="{{ $log->exists ? route('work-logs.show', $log) : route('work-logs.index') }}"
           class="text-sm text-slate-600 hover:text-slate-800">Cancelar</a>
    </div>
</div>

<script>
    function workLogForm() {
        return {
            hours: document.getElementById('hours') ? document.getElementById('hours').value : '',
            startTime: '',
            endTime: '',
            hoursManuallyEdited: {{ $log->exists ? 'true' : 'false' }},
            calcHours() {
                // Solo autocalcular si el usuario no escribió las horas a mano.
                if (this.hoursManuallyEdited || !this.startTime || !this.endTime) return;
                const [sh, sm] = this.startTime.split(':').map(Number);
                const [eh, em] = this.endTime.split(':').map(Number);
                let diff = (eh * 60 + em) - (sh * 60 + sm);
                if (diff > 0) {
                    this.hours = (diff / 60).toFixed(2);
                }
            },
        };
    }
</script>
