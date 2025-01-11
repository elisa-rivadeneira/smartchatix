@extends('adminlte::page')

@section('content')
<div class="container">
    <h1>Crear Modelo</h1>
    <form action="{{ route('a-i-models.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="identifier">Identificador</label>
            <input type="text" class="form-control" id="identifier" name="identifier" required>
        </div>
        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
