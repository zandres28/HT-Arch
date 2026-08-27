@extends('layouts.app')

@section('title', 'Nuevo proyecto')

@section('content')
    <div class="max-w-3xl">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <form method="POST" action="{{ route('projects.store') }}">
                @include('projects._form')
            </form>
        </div>
    </div>
@endsection
