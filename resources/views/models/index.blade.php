@extends('adminlte::page')

@section('content')
<div class="container">
    <h1>Modelos</h1>
    <a href="{{ route('a-i-models.create') }}" class="btn btn-primary mb-3">Crear Modelo</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Identificador</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($models as $model)
            <tr>
                <td>{{ $model->id }}</td>
                <td>{{ $model->name }}</td>
                <td>{{ $model->identifier }}</td>
                <td>{{ $model->description }}</td>
                <td>
                    <a href="{{ route('a-i-models.edit', $model->id) }}" class="btn btn-warning">Editar</a>
                    <form action="{{ route('a-i-models.destroy', $model->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
