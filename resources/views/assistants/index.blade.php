@extends('adminlte::page')

@section('content')
<div class="container">
    <h1 class="my-4">Lista de Asistentes</h1>
    <h4 >Tokens Usados : {{  $user->total_tokens_used }}</h4>

    <!-- Botón para crear un nuevo asistente -->
    <a href="{{ route('assistants.create') }}" class="btn btn-primary mb-4">Crear Asistente</a>
    
    <!-- Mensaje de éxito -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <!-- Tabla de asistentes -->
    <div class="card">
        <div class="card-header">Asistentes</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="20%">Nombre</th>
                        <th width="20%">Modelo</th>
                        <th width="35%">Prompt</th>
                        <th width="20%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assistants as $assistant)
                        <tr>
                            <td>{{ $assistant->id }}</td>
                            <td>{{ $assistant->name }}</td>
                            <td>{{ $assistant->model_name }}</td>
                            <td>{{ Str::limit($assistant->prompt, 50) }}</td>
                            <td>
                                <!-- Opciones para ver, editar y eliminar -->
                                <a href="{{ route('assistants.show', $assistant->id) }}" class="btn btn-info btn-sm">Chat</a>
                                <a href="{{ route('assistants.edit', $assistant->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                
                                <!-- Formulario para eliminar -->
                                <form action="{{ route('assistants.destroy', $assistant->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este asistente?');">x</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay asistentes disponibles.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection