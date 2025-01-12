@extends('adminlte::page')

@section('title', 'Crear Asistente')

@section('content')
    <div class="container">
        <h1>Crear Asistente Virtual</h1>
        <form action="{{ route('assistants.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Nombre del Asistente</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>

            <div class="form-group">
            <label for="type">Tipo de Servicio</label>
                <select name="type" id="type" class="form-control">
                <option value="curso">Curso</option>
                <option value="producto">Producto</option>
                <option value="servicio">Servicio</option>
                </select>
            </div>

            <div class="form-group">
                <label for="name">WhatsApp Contacto Humano</label>
                <input type="text" name="whatsapp_number" id="whatsapp_number" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="prompt">Prompt</label>
                <textarea name="prompt" id="prompt" class="form-control" required></textarea>
            </div>

            <div class="form-group">
                <label for="model_id">Modelo</label>
                <select name="model_id" id="model_id" class="form-control">
                    @foreach ($models as $model)
                        <option value="{{ $model->id }}" {{ old('model_id', $assistant->model_id ?? '') == $model->id ? 'selected' : '' }}>
                            {{ $model->name }}
                        </option>
                    @endforeach
                </select>
            </div>


            <button type="submit" class="btn btn-primary">Crear Asistente</button>
        </form>
    </div>
@endsection