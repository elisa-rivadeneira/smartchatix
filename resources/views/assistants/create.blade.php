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
                <label for="name">WhatsApp Contacto Humano</label>
                <input type="text" name="whatsapp_number" id="whatsapp_number" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="prompt">Prompt</label>
                <textarea name="prompt" id="prompt" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Crear Asistente</button>
        </form>
    </div>
@endsection