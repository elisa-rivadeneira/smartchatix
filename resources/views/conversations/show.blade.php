@extends('adminlte::page')

@section('content')
    <div class="container">
        <h1>Detalles de la Conversacion</h1>
        <p><strong>Nombre Asistente:</strong> {{ $conversation->assistant_name }}</p>
        <p><strong>Hora Conversacion:</strong> {{ $conversation->created_at }}</p>

        
        <h3>Uso de Tokens</h3>
        <p><strong>Total de Tokens:</strong> {{ $conversation->total_tokens }}</p>
 

        {{-- Historial del Chat --}}
        <h3>Historial del Chat</h3>
        <div id="chat-history">
            @foreach($messages as $chat)
            <div class="mb-2">
                @if($chat->sender == 'user')
                    <p><strong>Usuario:</strong> {!! $chat->message !!}</p>
                @elseif($chat->sender == 'assistant')
                    <p><strong>Asistente:</strong> {!! $chat->message !!}</p>
                @endif
                <hr>
            </div>
            @endforeach
        </div>

    </div>
@endsection


