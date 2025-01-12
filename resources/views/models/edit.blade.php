@extends('adminlte::page')

@section('content')
<div class="container">
    <h1>Editar Modelo</h1>
    <form action="{{ route('a-i-models.update', $model->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $model->name }}" required>
        </div>
        <div class="form-group">
            <label for="identifier">Identificador</label>
            <input type="text" class="form-control" id="identifier" name="identifier" value="{{ $model->identifier }}" required>
        </div>
        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea class="form-control" id="description" name="description">{{ $model->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>

        
    </form>
</div>
@endsection
