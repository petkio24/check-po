{{-- resources/views/pc-checks/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Результат проверки ПК')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $pcCheck->check_name }}</h1>
            <p class="page-subtitle">
                @if($pcCheck->pc_name)
                    ПК: {{ $pcCheck->pc_name }}
                @endif
                @if($pcCheck->pc_ip)
                    ({{ $pcCheck->pc_ip }})
                @endif
                | Проверка от {{ $pcCheck->created_at->format('d.m.Y H:i') }}
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('pc-checks.export', $pcCheck) }}" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Экспорт CSV
            </a>
            <a href="{{ route('pc-checks.create') }}" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Новая проверка
            </a>
        </div>
    </div>

    <!-- Сводка -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-label">Всего программ</div>
            <div class="summary-value">{{ $pcCheck->total_software }}</div>
        </div>
        <div class="summary-card legitimate">
            <div class="summary-label">Разрешено</div>
            <div class="summary-value">{{ $stats['legitimate'] }}</div>
            <div class="summary-percent">{{ $stats['compliance_percent'] }}%</div>
        </div>
        <div class="summary-card warning">
            <div class="summary-label">Несовпадение версий</div>
            <div class="summary-value">{{ $stats['version_mismatch'] }}</div>
        </div>
        <div class="summary-card danger">
            <div class="summary-label">Не разрешено</div>
            <div class="summary-value">{{ $stats['illegitimate'] }}</div>
        </div>
    </div>

    <!-- Прогресс-бар соответствия -->
    <div class="compliance-bar">
        <div class="compliance-label">Уровень легитимности</div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $stats['compliance_percent'] }}%"></div>
        </div>
        <div class="compliance-value">{{ $stats['compliance_percent'] }}%</div>
    </div>

    <!-- Таблица результатов -->
    <div class="results-table-container">
        <table class="results-table">
            <thead>
            <tr>
                <th>Программа</th>
                <th>Версия</th>
                <th>Поставщик</th>
                <th>Статус</th>
                <th>Детали</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                <tr class="status-{{ $item->status }}">
                    <td class="program-name">{{ $item->program_name }}</td>
                    <td class="program-version">{{ $item->version ?: '—' }}</td>
                    <td class="program-vendor">{{ $item->vendor ?: '—' }}</td>
                    <td>
                        @if($item->status === 'legitimate')
                            <span class="status-legitimate-badge">Разрешено</span>
                        @elseif($item->status === 'version_mismatch')
                            <span class="status-version-badge">Несовпадение версии</span>
                        @else
                            <span class="status-illegitimate-badge">Не разрешено</span>
                        @endif
                    </td>
                    <td class="details">
                        @php
                            $details = json_decode($item->match_details, true);
                        @endphp
                        @if($item->status === 'version_mismatch' && isset($details['expected_version']))
                            <span class="detail-text">Ожидалась: {{ $details['expected_version'] }}</span>
                            @if(isset($details['is_newer']) && $details['is_newer'])
                                <span class="detail-note">(новее)</span>
                            @elseif(isset($details['is_older']) && $details['is_older'])
                                <span class="detail-note">(старше)</span>
                            @endif
                        @elseif($item->status === 'illegitimate' && isset($details['reason']))
                            <span class="detail-text">{{ $details['reason'] }}</span>
                        @else
                            <span class="detail-text">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-state">Нет данных</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        {{ $items->links() }}
    </div>
@endsection
