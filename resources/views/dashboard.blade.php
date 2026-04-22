{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Обзор')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Панель управления</h1>
        <p class="page-subtitle">Контроль легитимности программного обеспечения</p>
    </div>

    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Всего в реестре</div>
            <div class="stat-value">{{ \App\Models\Report::count() }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Разрешённое ПО</div>
            <div class="stat-value">{{ \App\Models\AllowedSoftware::count() }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Последняя проверка</div>
            <div class="stat-value">
                {{ \App\Models\Report::latest()->first()?->created_at->format('d.m.Y') ?? '—' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Выявлено нарушений</div>
            <div class="stat-value">
                {{ \App\Models\Report::latest()->first()?->illegitimate_count ?? 0 }}
            </div>
        </div>
    </div>

    <!-- Быстрые действия -->
    <div class="action-grid">
        <a href="{{ route('reports.create') }}" class="action-card">
            <div class="action-title">Новая проверка</div>
            <div class="action-description">Загрузить реестр для анализа</div>
        </a>

        <a href="{{ route('allowed-software.create') }}" class="action-card">
            <div class="action-title">Добавить в реестр</div>
            <div class="action-description">Расширить список разрешённого ПО</div>
        </a>
    </div>

    <!-- Последние отчёты -->
    <div>
        <div class="section-header">
            <h2 class="section-title">Последние отчёты</h2>
            <a href="{{ route('reports.index') }}" class="section-link">Все отчёты</a>
        </div>

        <div class="card">
            <div class="table-container">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Файл</th>
                        <th>Записей</th>
                        <th>Разрешено</th>
                        <th>Несовпадение</th>
                        <th>Нарушения</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse(\App\Models\Report::latest()->take(5)->get() as $report)
                        <tr>
                            <td>{{ $report->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $report->file_name }}</td>
                            <td>{{ $report->total_entries }}</td>
                            <td class="badge-success" style="background: none; color: #22543d;">{{ $report->legitimate_count }}</td>
                            <td class="badge-warning" style="background: none; color: #7c2d12;">{{ $report->version_mismatch_count }}</td>
                            <td class="badge-danger" style="background: none; color: #742a2a;">{{ $report->illegitimate_count }}</td>
                            <td>
                                <a href="{{ route('reports.show', $report) }}" class="btn btn-secondary btn-sm">Детали</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--rosatom-text-light);">
                                Нет созданных отчётов
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
