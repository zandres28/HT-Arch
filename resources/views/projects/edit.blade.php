@extends('layouts.app')

@section('title', 'Editar proyecto')

@section('content')
    <div class="max-w-3xl">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <form method="POST" action="{{ route('projects.update', $project) }}">
                @method('PUT')
                @include('projects._form')
            </form>
        </div>
    </div>
@endsection
