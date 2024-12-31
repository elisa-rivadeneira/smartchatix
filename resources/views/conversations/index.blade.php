{{-- resources/views/conversations/index.blade.php --}}
@extends('adminlte::page')

@section('content')
    <h1>Conversations</h1>
    <a href="{{ route('conversations.create') }}" class="btn btn-primary">Create New Conversation</a>
    

        <!-- Mensaje de éxito -->
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif
    
        <div class="card">
        <div class="card-header">Conversaciones</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fecha y Hora</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conversation)
                        <tr>
                            <td>{{ $conversation->id }}</td>
                            <td>{{ $conversation->assistant_name }}</td>
                            <td>{{ $conversation->created_at }}</td>
                            <td>
                            <a href="{{ route('conversations.show', $conversation->id) }}" class="btn btn-info btn-sm">Ver</a>
                            <!-- Formulario para eliminar -->
                            <form action="{{ route('conversations.destroy', $conversation->id) }}" method="POST" style="display:inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta conversación?');">x</button>
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


