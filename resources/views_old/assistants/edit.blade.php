@extends('adminlte::page')

@section('content')
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

<!-- Formulario para eliminar el documento -->
@if ($document)
<div class="mb-3">
        <label class="form-label">Documento Asociado:</label>
        <div class="d-flex align-items-center">
            <p class="m-0 me-2"><a href="{{ asset('storage/' . $document->path) }}" target="_blank">{{ $document->filename }}</a></p>
            <!-- Botón de Eliminar -->
            <form action="{{ route('documents.destroy', $document->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="margin-left: 20px;">Eliminar</button>
            </form>
        </div>
    </div>

@endif

<!-- Formulario para subir un nuevo documento -->
<form action="{{ route('assistants.upload-document', ['assistant' => $assistant->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" required>
    <input type="hidden" name="assistant_id" value="{{ $assistant->id }}">
    <button type="submit">Subir Documento</button>
</form>
@endsection