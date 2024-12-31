@extends('adminlte::page')

@section('content')
    <div class="container">
        <h1>Detalles del Asistente</h1>
        <p><strong>Nombre:</strong> {{ $assistant->nombre_asistente }}</p>
        <p><strong>Información:</strong> {{ $assistant->informacion }}</p>
        
        {{-- Historial del Chat --}}
        <h3>Historial del Chat</h3>
        <div id="chat-history">
            @foreach($chatHistories as $chat)
                <div class="mb-2">
                    <p><strong>Usuario:</strong> {{ $chat->user_message }}</p>
                    <p><strong>Asistenteee:</strong> {{ $chat->assistant_response }}</p>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
 
            console.log("El script de JavaScript se ha cargado correctamente");

            $('#chat-form').on('submit', function(event) {
                event.preventDefault(); // Evitar el envío tradicional del formulario
                console.log("Formulario enviado vía AJAX"); // Verifica en la consola del navegador

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
                        
                        // Agregar la nueva interacción al historial de chat
                        $('#chat-history').append(
                            '<div class="mb-2">' +
                                '<p><strong>Usuario:</strong> ' + response.user_message + '</p>' +
                                '<p><strong>Asistentee_e_e:</strong> ' + response.assistant_response + '</p>' +
                                '<hr>' +
                            '</div>'
                        );
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
