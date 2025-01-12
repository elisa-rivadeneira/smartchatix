@extends('adminlte::page')

@section('content')
    <div class="container">
        <h1>Crear Curso</h1>
        <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="title">Título del Curso</label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
            </div>

            <div class="form-group mt-3">
                <label for="description">Descripción</label>
                <textarea name="description" id="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>

            <div class="form-group mt-3">
                <label for="price">Precio</label>
                <input type="number" name="price" id="price" class="form-control" step="0.01" value="{{ old('price') }}" required>
            </div>

            <div class="form-group mt-3">
                <label for="duration">Duración (en horas)</label>
                <input type="number" name="duration" id="duration" class="form-control" value="{{ old('duration') }}" required>
            </div>

            <div class="form-group mt-3">
                <label for="category">Categoría</label>
                <input type="text" name="category" id="category" class="form-control" value="{{ old('category') }}">
            </div>

            <div class="form-group mt-3">
                <label for="teacher">Profesor</label>
                <input type="text" name="teacher" id="teacher" class="form-control" value="{{ old('teacher') }}" required>
            </div>

            <div class="form-group mt-3">
                <label for="description_teacher">Descripción del Profesor</label>
                <textarea name="description_teacher" id="description_teacher" class="form-control" rows="4">{{ old('description_teacher') }}</textarea>
            </div>


            <div class="form-group">
            <label for="modalidad">Modalidad</label>
            <select name="modalidad" id="modalidad" class="form-control">
            <option value="Presencial">Presencial</option>
            <option value="Virtual">Virtual</option>
            <option value="Mixto">Mixto</option>
            </select>
            </div>

            <div class="form-group">
            <label for="imagen">Imagen del Curso</label>
            <input type="file" name="imagen" id="imagen" class="form-control">
            </div>


            <button type="submit" class="btn btn-primary mt-4">Crear Curso</button>
        </form>
    </div>
@endsection
