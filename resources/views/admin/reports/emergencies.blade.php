@extends('layouts.app')

@section('title', 'Reporte de Emergencias')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-ambulance text-danger me-2"></i>Reporte de Emergencias
            </h1>
            <p class="text-muted mb-0">Estadísticas de emergencias atendidas por las guardias</p>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="month" class="form-select" onchange="this.form.submit()">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                        </option>
                    @endforeach
                </select>
                <select name="year" class="form-select" onchange="this.form.submit()">
                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <select name="guardia_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Todas las Guardias</option>
                    @foreach($guardias as $guardia)
                        <option value="{{ $guardia->id }}" {{ $guardiaId == $guardia->id ? 'selected' : '' }}>
                            {{ $guardia->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-ambulance fa-2x text-danger"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Emergencias</h6>
                            <h3 class="mb-0">{{ $kpis['total_emergencies'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Período</h6>
                            <h5 class="mb-0">{{ Carbon\Carbon::create()->month($month)->locale('es')->monthName }} {{ $year }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-alt fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Guardia Filtrada</h6>
                            <h5 class="mb-0">{{ $kpis['guardia_filter'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-key fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Claves Distintas</h6>
                            <h3 class="mb-0">{{ $topKeys->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Emergencias por Guardia</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartByGuardia" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Emergencias Mensuales ({{ $year }})</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartMonthly" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Emergencias por Hora del Día</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartByHour" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>Top 5 Claves Más Concurridas</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartTopKeys" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row">
        <!-- Vehículos más utilizados -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-car me-2"></i>Vehículos Más Utilizados</h5>
                    <small class="text-muted">Ordenado de menor a mayor</small>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehiclesUsed as $vehicle)
                                <tr>
                                    <td>{{ $vehicle['vehicle'] }}</td>
                                    <td class="text-center">{{ $vehicle['total'] }}</td>
                                    <td class="text-center">
                                        @if($kpis['total_emergencies'] > 0)
                                            {{ round(($vehicle['total'] / $kpis['total_emergencies']) * 100, 1) }}%
                                        @else
                                            0%
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Sin datos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sistema de Puntos -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Sistema de Puntos</h5>
                    <small>10-0-1 = 1 punto | 10-4-1 = 2 puntos</small>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Clave</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Pts/Emerg</th>
                                <th class="text-center">Total Pts</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pointsByKey as $key)
                                <tr>
                                    <td>
                                        <span class="badge bg-danger">{{ $key['key'] }}</span>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($key['description'], 25) }}</small>
                                    </td>
                                    <td class="text-center">{{ $key['total'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">{{ $key['points_per_emergency'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-success">{{ $key['total_points'] }}</strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Sin datos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Claves -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>Top 5 Claves</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Clave</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topKeys as $index => $key)
                                <tr>
                                    <td>
                                        @if($index < 3)
                                            <i class="fas fa-medal text-{{ ['warning', 'secondary', 'danger'][$index] }}"></i>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $key['key'] }}</span>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($key['description'], 30) }}</small>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ $key['total'] }}</strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Sin datos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Emergencias por Guardia - Tabla Detallada -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Detalle por Guardia</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Guardia</th>
                                    <th class="text-center">Total Emergencias</th>
                                    <th class="text-center">% del Total</th>
                                    <th>Progreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($statsByGuardia as $stat)
                                    <tr>
                                        <td>
                                            <i class="fas fa-shield-alt text-primary me-2"></i>
                                            {{ $stat['guardia'] }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">{{ $stat['total'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($kpis['total_emergencies'] > 0)
                                                {{ round(($stat['total'] / $kpis['total_emergencies']) * 100, 1) }}%
                                            @else
                                                0%
                                            @endif
                                        </td>
                                        <td style="width: 30%;">
                                            <div class="progress">
                                                @if($kpis['total_emergencies'] > 0)
                                                    @php
                                                        $percentage = round(($stat['total'] / $kpis['total_emergencies']) * 100);
                                                        $color = $percentage > 50 ? 'danger' : ($percentage > 25 ? 'warning' : 'success');
                                                    @endphp
                                                    <div class="progress-bar bg-{{ $color }}" style="width: {{ $percentage }}%">
                                                        {{ $percentage }}%
                                                    </div>
                                                @else
                                                    <div class="progress-bar bg-secondary" style="width: 0%">0%</div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Sin datos</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart defaults
    Chart.defaults.font.family = "'Nunito', 'Segoe UI', 'Roboto', sans-serif";
    Chart.defaults.color = '#666';

    // Chart: Emergencies by Guardia
    new Chart(document.getElementById('chartByGuardia'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($charts['by_guardia']['labels']) !!},
            datasets: [{
                label: 'Emergencias',
                data: {!! json_encode($charts['by_guardia']['data']) !!},
                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Chart: Monthly
    new Chart(document.getElementById('chartMonthly'), {
        type: 'line',
        data: {
            labels: {!! json_encode($charts['monthly']['labels']) !!},
            datasets: [{
                label: 'Emergencias',
                data: {!! json_encode($charts['monthly']['data']) !!},
                borderColor: 'rgba(0, 123, 255, 1)',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Chart: By Hour
    new Chart(document.getElementById('chartByHour'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($charts['by_hour']['labels']) !!},
            datasets: [{
                label: 'Emergencias',
                data: {!! json_encode($charts['by_hour']['data']) !!},
                backgroundColor: 'rgba(23, 162, 184, 0.8)',
                borderColor: 'rgba(23, 162, 184, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { title: { display: true, text: 'Hora del día' } }
            }
        }
    });

    // Chart: Top Keys
    new Chart(document.getElementById('chartTopKeys'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($charts['top_keys']['labels']) !!},
            datasets: [{
                data: {!! json_encode($charts['top_keys']['data']) !!},
                backgroundColor: [
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(0, 123, 255, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(111, 66, 193, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
</script>
@endpush
