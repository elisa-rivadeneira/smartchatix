@extends('adminlte::page')

@section('content')
<div class="container">
    <h1 class="my-4">Editar Asistente</h1>
    
    <!-- Formulario de edición -->
    <div class="card">
        <div class="card-header">Editar Asistente: {{ $assistant->name }}</div>
        <div class="card-body">
            <form action="{{ route('assistants.update', $assistant->id) }}" method="POST">
                @csrf
                @method('PUT') <!-- Spoofing del método PUT para actualizar -->

                <!-- Campo de Nombre -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $assistant->name) }}" required>
                </div>

                <!-- Campo de Prompt -->
                <div class="mb-3">
                    <label for="prompt" class="form-label">Prompt</label>
                    <textarea name="prompt" id="prompt" class="form-control" rows="4" required>{{ old('prompt', $assistant->prompt) }}</textarea>
                </div>
                
                <!-- Enlace al documento -->
                @if ($document)
                    <div class="mb-3">
                        <label class="form-label">Documento Asociado:</label>
                        <p><a href="{{ asset('storage/' . $document->path) }}" target="_blank">{{ $document->filename }}</a></p>
                        <!-- Botón de Eliminar -->
                        <form action="{{ route('documents.delete', $document->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm ml-2">Eliminar</button>
                                    </form>

                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <!-- Botón de Guardar Cambios -->
                <button type="submit" class="btn btn-success">Guardar Cambios</button>
                <a href="{{ route('assistants.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>

            <form action="{{ route('assistants.upload-document', ['assistant' => $assistant->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" required>
                        <input type="hidden" name="assistant_id" value="{{ $assistant->id }}">
                        <button type="submit">Subir Documento</button>
                    </form>
        </div>
    </div>
</div>
@endsection