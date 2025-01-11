@extends('adminlte::page')



@section('content')
<div class="container">
    <div class="row">
        <!-- Título -->
        <div class="col-12 mb-4">
            <h1 class="text-center">Bienvenido, {{ Auth::user()->name }} 👋</h1>
            <p class="text-center">Aquí tienes un resumen de tu actividad reciente.</p>
        </div>
    </div>

    <!-- Resumen de Actividad -->
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Asistentes Activos</h5>
                    <h3>{{ $asistentesCount ?? 0 }}</h3>
                    <p>Asistentes creados hasta ahora.</p>
                    <a href="{{ route('assistants.index') }}" class="btn btn-primary">Gestionar Asistentes</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Conversaciones Recientes</h5>
                    <h3>{{ $conversacionesCount ?? 0 }}</h3>
                    <p>Chats registrados este mes.</p>
                    <a href="{{ route('conversations.index') }}" class="btn btn-success">Ver Conversaciones</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Mensajes Totales</h5>
                    <h3>{{ $interaccionesTotales ?? 0 }}</h3>
                    <p>Respuestas generadas por tus asistentes.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Actividad Mensual</h5>
                </div>
                <div class="card-body">
                    <canvas id="actividadChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Atajos Rápidos -->
    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Crear Nuevo Asistente</h5>
                    <a href="{{ route('assistants.create') }}" class="btn btn-primary">Crear</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Configurar Perfil</h5>
                    <a href="#" class="btn btn-secondary">Editar</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Soporte Técnico</h5>
                    <a href="#" class="btn btn-danger">Contactar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir librerías de gráficos si usas Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('actividadChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($actividadLabels ?? []) !!}, // Rellena con las etiquetas de tiempo
                datasets: [{
                    label: 'Interacciones',
                    data: {!! json_encode($actividadData ?? []) !!}, // Rellena con los datos de interacciones
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                    }
                }
            }
        });
    });
</script>
@endsection
