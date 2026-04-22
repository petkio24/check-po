{{-- resources/views/allowed-software/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Редактировать ПО')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Редактирование программного обеспечения</h1>
            <p class="page-subtitle">Измените информацию о разрешённом ПО</p>
        </div>
        <a href="{{ route('allowed-software.index') }}" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Назад к списку
        </a>
    </div>

    <div class="form-container">
        <form action="{{ route('allowed-software.update', $allowedSoftware) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-card">
                <div class="card-title">Основная информация</div>

                <div class="form-group">
                    <label class="form-label required">Название программы</label>
                    <input type="text" name="name" class="form-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $allowedSoftware->name) }}" required>
                    @error('name')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Версия</label>
                        <input type="text" name="version" class="form-input"
                               value="{{ old('version', $allowedSoftware->version) }}">
                        <div class="form-hint">Оставьте пустым, если версия не важна</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Производитель</label>
                        <input type="text" name="vendor" class="form-input"
                               value="{{ old('vendor', $allowedSoftware->vendor) }}">
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="card-title">Дополнительная информация</div>

                <div class="form-group">
                    <label class="form-label">Примечания</label>
                    <textarea name="notes" class="form-textarea" rows="4">{{ old('notes', $allowedSoftware->notes) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" {{ $allowedSoftware->is_active ? 'checked' : '' }}>
                        <span>Активно (участвует в проверках)</span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('allowed-software.index') }}" class="btn btn-secondary">Отмена</a>
                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Сохранить изменения
                </button>
            </div>
        </form>
    </div>
@endsection
