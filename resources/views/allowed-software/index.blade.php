{{-- resources/views/allowed-software/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Реестр разрешённого ПО')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Реестр разрешённого ПО</h1>
            <p class="page-subtitle">Управление списком легитимного программного обеспечения</p>
        </div>
        <a href="{{ route('allowed-software.create') }}" class="btn btn-primary btn-lg">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Добавить ПО
        </a>
    </div>

    <!-- Фильтры поиска -->
    <div class="filters-panel">
        <div class="filters-row">
            <div class="filter-input">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="searchFilter" placeholder="Поиск по названию..." class="filter-search">
            </div>
            <select id="statusFilter" class="filter-select">
                <option value="all">Все статусы</option>
                <option value="active">Активные</option>
                <option value="inactive">Неактивные</option>
            </select>
            <button id="resetFilters" class="btn btn-secondary">Сбросить</button>
        </div>
    </div>

    <div class="software-table-container">
        <table class="software-table">
            <thead>
            <tr>
                <th>Название</th>
                <th>Версия</th>
                <th>Поставщик</th>
                <th>Дата добавления</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            @forelse($software as $item)
                <tr data-status="{{ $item->is_active ? 'active' : 'inactive' }}">
                    <td class="software-name">
                        <div class="software-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="4" width="16" height="16" rx="2"/>
                                <line x1="8" y1="8" x2="16" y2="16"/>
                                <line x1="16" y1="8" x2="8" y2="16"/>
                            </svg>
                        </div>
                        {{ $item->name }}
                    </td>
                    <td class="software-version">{{ $item->version ?: '—' }}</td>
                    <td class="software-vendor">{{ $item->vendor ?: '—' }}</td>
                    <td class="software-date">{{ $item->created_at->format('d.m.Y') }}</td>
                    <td>
                        @if($item->is_active)
                            <span class="status-badge active">Активен</span>
                        @else
                            <span class="status-badge inactive">Неактивен</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('allowed-software.edit', $item) }}" class="action-btn edit" title="Редактировать">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 3l4 4-7 7H10v-4l7-7z"/>
                                    <path d="M4 20h16"/>
                                </svg>
                            </a>
                            <form action="{{ route('allowed-software.destroy', $item) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete" onclick="return confirm('Удалить это ПО из реестра?')" title="Удалить">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        <div class="empty-icon">📋</div>
                        <div class="empty-text">Реестр разрешённого ПО пуст</div>
                        <a href="{{ route('allowed-software.create') }}" class="btn btn-primary btn-sm mt-3">Добавить первое ПО</a>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        {{ $software->links() }}
    </div>

    @push('scripts')
        <script>
            // Фильтрация таблицы
            const searchFilter = document.getElementById('searchFilter');
            const statusFilter = document.getElementById('statusFilter');
            const resetBtn = document.getElementById('resetFilters');
            const tableRows = document.querySelectorAll('.software-table tbody tr');

            function filterTable() {
                const searchTerm = searchFilter.value.toLowerCase();
                const statusValue = statusFilter.value;

                tableRows.forEach(row => {
                    const name = row.querySelector('.software-name')?.textContent.toLowerCase() || '';
                    const vendor = row.querySelector('.software-vendor')?.textContent.toLowerCase() || '';
                    const rowStatus = row.dataset.status;

                    const matchesSearch = name.includes(searchTerm) || vendor.includes(searchTerm);
                    const matchesStatus = statusValue === 'all' || rowStatus === statusValue;

                    row.style.display = matchesSearch && matchesStatus ? '' : 'none';
                });
            }

            searchFilter.addEventListener('input', filterTable);
            statusFilter.addEventListener('change', filterTable);
            resetBtn.addEventListener('click', () => {
                searchFilter.value = '';
                statusFilter.value = 'all';
                filterTable();
            });
        </script>
    @endpush
@endsection
