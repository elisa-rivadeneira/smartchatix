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

    <div class="form-group">
    <label for="type">Tipo de Servicio</label>
    <select name="type" id="type" class="form-control">
        <option value="curso" {{ $assistant->type == 'curso' ? 'selected' : '' }}>Curso</option>
        <option value="producto" {{ $assistant->type == 'producto' ? 'selected' : '' }}>Producto</option>
        <option value="servicio" {{ $assistant->type == 'servicio' ? 'selected' : '' }}>Servicio</option>
    </select>
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


    <div class="mb-3">
        <label for="name" class="form-label">Whatsapp Number</label>
        <input type="text" name="whatsapp_number" id="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $assistant->whatsapp_number) }}" required>
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

        <!-- Mostrar mensaje de éxito -->
        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
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




    <!-- Formulario para subir un nuevo documento -->
    <form action="{{ route('assistants.upload-training-document', ['assistant' => $assistant->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <input type="file" name="file" required class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Subir Documento de Entrenamiento</button>
</form>



<div class="card">
    <div class="card-header">
        <h5>Estado del Fine-Tuning</h5>
    </div>
    <div class="card-body" id="fineTuningStatus">
        <p><strong>Estado:</strong> <span id="statusText">Cargando...</span></p>
        <button id="checkStatus">Ver estado</button>
        <p id="statusText">Estado actual no disponible.</p>
        <ul id="eventsList"></ul>
    </div>
</div>

@endsection

@section('js')
    <script>
        document.getElementById('checkStatus').addEventListener('click', function () {
            console.log('En el check status de ver el monitor de los fine-tunings');
            const url = '/assistants/{{ $assistant->id }}/monitor-finetuning';
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const statusText = document.getElementById('statusText');
                    const eventsList = document.getElementById('eventsList');

                    // Limpiar contenido previo
                    eventsList.innerHTML = '';
                    console.log('data:::', data);
                    if (data.model_name) {
                        const modelNameElement = document.createElement('p');
                        modelNameElement.textContent = `Modelo fine-tuneado: ${data.model_name}`;
                        eventsList.appendChild(modelNameElement);
                    }


                    if (data.status && data.status.status) {
                        // Mostrar el estado del fine-tuning
                        statusText.textContent = `Estado: ${data.status.status}`;
                        
                        // Mostrar eventos si existen
                        if (data.events && data.events.length > 0) {
                            data.events.forEach(event => {
                                const listItem = document.createElement('li');
                                listItem.textContent = `${event.message}`;
                                eventsList.appendChild(listItem);
                            });
                        } else {
                            const noEventsItem = document.createElement('li');
                            noEventsItem.textContent = 'No hay eventos disponibles.';
                            eventsList.appendChild(noEventsItem);
                        }
                    } else if (data.error) {
                        statusText.textContent = `Error: ${data.error}`;
                    } else {
                        statusText.textContent = 'Error al consultar el estado.';
                    }
                })
                .catch(error => {
                    console.error('Error al consultar el estado:', error);
                    document.getElementById('statusText').textContent = 'Error al consultar el estado.';
                });
        });
    </script>
@endsection
