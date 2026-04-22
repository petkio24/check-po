@extends('layouts.app')

@section('title', 'Реестр ПО')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Реестр разрешённого ПО</h1>
            <p class="page-subtitle">Управление списком легитимного программного обеспечения</p>
        </div>
        <a href="{{ route('allowed-software.create') }}" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Добавить ПО
        </a>
    </div>

    <!-- Фильтры -->
    <div class="filters-panel">
        <div class="filters-row">
            <input type="text" id="searchFilter" placeholder="Поиск по названию..." class="filter-search">
            <select id="statusFilter" class="filter-select">
                <option value="all">Все статусы</option>
                <option value="active">Активные</option>
                <option value="inactive">Неактивные</option>
            </select>
            <button id="resetFilters" class="btn btn-secondary">Сбросить</button>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
            <tr>
                <th>Название</th>
                <th>Версия</th>
                <th>Поставщик</th>
                <th>Дата</th>
                <th>Статус</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($software as $item)
                <tr data-status="{{ $item->is_active ? 'active' : 'inactive' }}">
                    <td><strong>{{ $item->name }}</strong></td>
                    <td>{{ $item->version ?: '—' }}</td>
                    <td>{{ $item->vendor ?: '—' }}</td>
                    <td>{{ $item->created_at->format('d.m.Y') }}</td>
                    <td>
                        @if($item->is_active)
                            <span class="badge badge-success">Активен</span>
                        @else
                            <span class="badge badge-neutral">Неактивен</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('allowed-software.edit', $item) }}" class="action-btn edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 3l4 4-7 7H10v-4l7-7z"/>
                                    <path d="M4 20h16"/>
                                </svg>
                            </a>
                            <form action="{{ route('allowed-software.destroy', $item) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete" onclick="return confirm('Удалить?')">
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
                        Реестр разрешённого ПО пуст
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
            const searchFilter = document.getElementById('searchFilter');
            const statusFilter = document.getElementById('statusFilter');
            const resetBtn = document.getElementById('resetFilters');
            const rows = document.querySelectorAll('.table tbody tr');

            function filterTable() {
                const search = searchFilter.value.toLowerCase();
                const status = statusFilter.value;

                rows.forEach(row => {
                    const name = row.cells[0]?.textContent.toLowerCase() || '';
                    const rowStatus = row.dataset.status;
                    const matchSearch = name.includes(search);
                    const matchStatus = status === 'all' || rowStatus === status;
                    row.style.display = matchSearch && matchStatus ? '' : 'none';
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
