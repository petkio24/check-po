@extends('layouts.app')

@section('title', 'Обзор')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Панель управления</h1>
        <p class="page-subtitle">Система проверки легитимности программного обеспечения</p>
    </div>

    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Всего проверок ПК</div>
            <div class="stat-value">{{ \App\Models\PcCheck::count() }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Разрешённое ПО</div>
            <div class="stat-value">{{ \App\Models\AllowedSoftware::count() }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Последняя проверка</div>
            <div class="stat-value">
                {{ \App\Models\PcCheck::latest()->first()?->created_at->format('d.m.Y') ?? '—' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Выявлено нарушений</div>
            <div class="stat-value">
                {{ \App\Models\PcCheck::latest()->first()?->illegitimate_count ?? 0 }}
            </div>
        </div>
    </div>

    <!-- Быстрые действия -->
    <div class="action-grid">
        <a href="{{ route('pc-checks.create') }}" class="action-card">
            <div class="action-title">Новая проверка ПК</div>
            <div class="action-description">Вставить список ПО из консоли для анализа</div>
        </a>

        <a href="{{ route('allowed-software.create') }}" class="action-card">
            <div class="action-title">Добавить в реестр</div>
            <div class="action-description">Расширить список разрешённого ПО</div>
        </a>
    </div>

    <!-- Последние проверки -->
    <div>
        <div class="section-header">
            <h2 class="section-title">Последние проверки</h2>
            <a href="{{ route('pc-checks.index') }}" class="section-link">Все проверки</a>
        </div>

        <div class="checks-list">
            @forelse(\App\Models\PcCheck::latest()->take(5)->get() as $check)
                <div class="check-card">
                    <div class="check-header">
                        <div>
                            <div class="check-title">{{ $check->pc_name ?: 'Проверка #' . $check->id }}</div>
                            <div class="check-meta">{{ $check->created_at->format('d.m.Y H:i') }}</div>
                        </div>
                        <div class="check-stats">
                            <div class="stat">
                                <span class="stat-label">Всего</span>
                                <span class="stat-value">{{ $check->total_software }}</span>
                            </div>
                            <div class="stat legitimate">
                                <span class="stat-label">Разрешено</span>
                                <span class="stat-value">{{ $check->legitimate_count }}</span>
                            </div>
                            <div class="stat warning">
                                <span class="stat-label">Несовпадение</span>
                                <span class="stat-value">{{ $check->version_mismatch_count }}</span>
                            </div>
                            <div class="stat danger">
                                <span class="stat-label">Нарушения</span>
                                <span class="stat-value">{{ $check->illegitimate_count }}</span>
                            </div>
                        </div>
                        <a href="{{ route('pc-checks.show', $check) }}" class="btn btn-sm btn-secondary">Детали</a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-text">Нет выполненных проверок</div>
                    <a href="{{ route('pc-checks.create') }}" class="btn btn-primary mt-3">Выполнить первую проверку</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection
