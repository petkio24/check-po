@extends('layouts.app')

@section('title', 'История проверок ПК')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">История проверок ПК</h1>
            <p class="page-subtitle">Все проверки легитимности ПО на компьютерах</p>
        </div>
        <a href="{{ route('pc-checks.create') }}" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Новая проверка
        </a>
    </div>

    <div class="checks-list">
        @forelse($checks as $check)
            <div class="check-card">
                <div class="check-header">
                    <div>
                        <div class="check-title">{{ $check->check_name }}</div>
                        <div class="check-meta">
                            {{ $check->created_at->format('d.m.Y H:i:s') }}
                            @if($check->pc_name)
                                • {{ $check->pc_name }}
                            @endif
                            @if($check->pc_ip)
                                • {{ $check->pc_ip }}
                            @endif
                        </div>
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
                    <div class="check-actions">
                        <a href="{{ route('pc-checks.show', $check) }}" class="btn btn-sm btn-secondary">Детали</a>
                        <a href="{{ route('pc-checks.export', $check) }}" class="btn btn-sm btn-secondary">Экспорт</a>
                        <form action="{{ route('pc-checks.destroy', $check) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить проверку?')">Удалить</button>
                        </form>
                    </div>
                </div>
                <div class="check-progress">
                    <div class="progress-bar">
                        <div class="progress-legitimate" style="width: {{ $check->total_software > 0 ? ($check->legitimate_count / $check->total_software) * 100 : 0 }}%"></div>
                        <div class="progress-version" style="width: {{ $check->total_software > 0 ? ($check->version_mismatch_count / $check->total_software) * 100 : 0 }}%"></div>
                        <div class="progress-illegitimate" style="width: {{ $check->total_software > 0 ? ($check->illegitimate_count / $check->total_software) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-text">Нет выполненных проверок</div>
                <a href="{{ route('pc-checks.create') }}" class="btn btn-primary mt-3">Выполнить первую проверку</a>
            </div>
        @endforelse
    </div>

    <div class="pagination-container">
        {{ $checks->links() }}
    </div>
@endsection
