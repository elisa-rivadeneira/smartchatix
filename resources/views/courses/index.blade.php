@extends('adminlte::page')

@section('content')
    <h1>Lista de Cursos</h1>
    <a href="{{ route('courses.create') }}" class="btn btn-primary">Crear Curso</a>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Profesor</th>
                <th>Precio</th>
                <th>Duración</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courses as $course)
                <tr>
                    <td>{{ $course->id }}</td>
                    <td>{{ $course->title }}</td>
                    <td>{{ $course->teacher }}</td>
                    <td>{{ $course->price }}</td>
                    <td>{{ $course->duration }} horas</td>
                    <td>
                        <a href="{{ route('courses.show', $course->id) }}" class="btn btn-info">Ver</a>
                        <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-warning">Editar</a>
                        <form action="{{ route('courses.destroy', $course->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
