@extends('adminlte::page')

@section('content')
    <div class="container">
        <h1>Editar Curso</h1>
        <form action="{{ route('courses.update', $course->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Título del Curso</label>
                <input type="text" name="title" id="title" class="form-control" value="{{ $course->title }}" required>
            </div>

            <div class="form-group mt-3">
                <label for="description">Descripción</label>
                <textarea name="description" id="description" class="form-control" rows="4">{{ $course->description }}</textarea>
            </div>

            <div class="form-group mt-3">
                <label for="price">Precio</label>
                <input type="number" name="price" id="price" class="form-control" step="0.01" value="{{ $course->price }}" required>
            </div>

            <div class="form-group mt-3">
                <label for="duration">Duración (en horas)</label>
                <input type="number" name="duration" id="duration" class="form-control" value="{{ $course->duration }}" required>
            </div>

            <div class="form-group mt-3">
                <label for="category">Categoría</label>
                <input type="text" name="category" id="category" class="form-control" value="{{ $course->category }}">
            </div>

            <div class="form-group mt-3">
                <label for="teacher">Profesor</label>
                <input type="text" name="teacher" id="teacher" class="form-control" value="{{ $course->teacher }}" required>
            </div>

            <div class="form-group mt-3">
                <label for="description_teacher">Descripción del Profesor</label>
                <textarea name="description_teacher" id="description_teacher" class="form-control" rows="4">{{ $course->description_teacher }}</textarea>
            </div>

            <div class="form-group">
            <label for="modalidad">Modalidad</label>
            <select name="modalidad" id="modalidad" class="form-control">
            <option value="Presencial" {{ $course->modalidad == 'Presencial' ? 'selected' : '' }}>Presencial</option>
            <option value="Virtual" {{ $course->modalidad == 'Virtual' ? 'selected' : '' }}>Virtual</option>
            <option value="Mixto" {{ $course->modalidad == 'Mixto' ? 'selected' : '' }}>Mixto</option>
            </select>
            </div>

            <div class="form-group">
            <label for="imagen">Imagen del Curso</label>
            <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
            @if ($course->imagen)
            <p>Imagen actual:</p>
            <img src="{{ Storage::url($course->imagen) }}" alt="Imagen del curso" style="max-width: 200px;">
            @endif
            </div>



            <button type="submit" class="btn btn-success mt-4">Actualizar Curso</button>
        </form>
    </div>
@endsection
