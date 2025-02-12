@extends('adminlte::page')

@section('content')
    <div class="container">
        <h1>Detalles del Asistente_</h1>
        <p><strong>Nombre:</strong> {{ $assistant->nombre_asistente }}</p>
        <p><strong>Información:</strong> {{ $assistant->informacion }}</p>
        
        {{-- Historial del Chat --}}
        <h3>Historial del Chat</h3>
        <div id="chat-history">
            @foreach($chatHistories as $chat)
                <div class="mb-2">
                    <p><strong>Usuario:</strong> {{ $chat->user_message }}</p>
                    <p><strong>Asistenteee:</strong><span class="assistant-response">{!! $chat->assistant_response !!}</span></p>
                    <hr>
                </div>
            @endforeach
        </div>

        {{-- Formulario de Pregunta --}}
        <form id="chat-form">
            @csrf
            <div class="form-group">
                <label for="user_input">Tu Pregunta</label>
                <input type="text" id="user_input" name="user_input" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <script>
        // Convertir el contenido Markdown a HTML cuando se cargue la página
        document.querySelectorAll('.assistant-response').forEach(function (element) {
            element.innerHTML = marked(element.innerHTML);
        });

        // Usar AJAX para enviar el formulario
        $(document).ready(function() {
            console.log("El script de JavaScript se ha cargado correctamente");

            $('#chat-form').on('submit', function(event) {
                event.preventDefault(); // Evitar el envío tradicional del formulario
                console.log("Formulario enviado vía AJAX");

                let userInput = $('#user_input').val();
                
                $.ajax({
                    url: "{{ route('assistants.generateResponse', $assistant->id) }}", // URL de la ruta para enviar la pregunta
                    method: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(), // CSRF token
                        user_input: userInput
                    },
                    dataType: 'json', // Asegurar que la respuesta se maneje como JSON
                    success: function(response) {
                        // Limpiar el campo de entrada
                        $('#user_input').val('');

                        // Convertir la respuesta del asistente (en Markdown) a HTML
                        var assistantResponseHtml = marked(response.assistant_response);

                        // Agregar la nueva interacción al historial de chat
                        $('#chat-history').append(
                            '<div class="mb-2">' +
                                '<p><strong>Usuario:</strong> ' + response.user_message + '</p>' +
                                '<p><strong>Asistenteee:</strong><span class="assistant-response">' + assistantResponseHtml + '</span></p>' +
                                '<hr>' +
                            '</div>'
                        );

                        // Volver a procesar el Markdown para las respuestas nuevas agregadas
                        document.querySelectorAll('.assistant-response').forEach(function (element) {
                            element.innerHTML = marked(element.innerHTML);
                        });
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                        alert('Ocurrió un error al procesar la solicitud.');
                    }
                });
            });
        });
    </script>
@endsection
