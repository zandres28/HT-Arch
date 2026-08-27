@extends('layouts.app')

@section('title', 'Respaldo')

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-800 mb-1">Generar respaldo</h3>
            <p class="text-sm text-slate-500 mb-4">Descarga un archivo <code class="px-1 bg-slate-100 rounded">.zip</code> con el volcado de la base de datos y todos tus archivos (evidencias y entregables).</p>
            <form method="POST" action="{{ route('backup.export') }}">
                @csrf
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg">
                    ⬇ Descargar respaldo completo
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-800 mb-1">Restaurar respaldo</h3>
            <p class="text-sm text-slate-500 mb-4">Selecciona un archivo <code class="px-1 bg-slate-100 rounded">.zip</code> generado previamente. <b class="text-red-600">Esto reemplazará</b> la base de datos y los archivos actuales.</p>
            <form method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="backup" accept=".zip" required
                       class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 mb-3">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium px-4 py-2.5 rounded-lg">
                    ⬆ Restaurar respaldo
                </button>
            </form>
        </div>
    </div>

    @if ($existing->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-5 mt-5">
            <h3 class="font-semibold text-slate-800 mb-3">Respaldos generados localmente</h3>
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach ($existing as $file)
                    <li class="py-2 flex justify-between text-slate-600">
                        <span>{{ $file->getFilename() }}</span>
                        <span class="text-slate-400">{{ number_format($file->getSize() / 1024, 1) }} KB · {{ date('d/m/Y H:i', $file->getMTime()) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-5 text-xs text-slate-400 leading-relaxed">
        <p>El respaldo incluye la base de datos MySQL y tus archivos. Guárdalo en un lugar seguro (otro disco o nube). La restauración sobrescribe los datos actuales.</p>
    </div>
@endsection
