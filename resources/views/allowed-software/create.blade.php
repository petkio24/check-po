{{-- resources/views/allowed-software/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Добавить ПО в реестр')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Добавление в реестр разрешённого ПО</h1>
            <p class="page-subtitle">Заполните информацию о программном обеспечении</p>
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
        <form action="{{ route('allowed-software.store') }}" method="POST">
            @csrf

            <div class="form-card">
                <div class="card-title">Основная информация</div>

                <div class="form-group">
                    <label class="form-label required">Название программы</label>
                    <input type="text" name="name" class="form-input @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="例如: 7-Zip, Adobe Acrobat Reader DC" required>
                    @error('name')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Версия</label>
                        <input type="text" name="version" class="form-input"
                               value="{{ old('version') }}" placeholder="例如: 23.01 (x64)">
                        <div class="form-hint">Оставьте пустым, если версия не важна</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Производитель</label>
                        <input type="text" name="vendor" class="form-input"
                               value="{{ old('vendor') }}" placeholder="例如: Igor Pavlov, Adobe Systems">
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="card-title">Дополнительная информация</div>

                <div class="form-group">
                    <label class="form-label">Примечания</label>
                    <textarea name="notes" class="form-textarea" rows="4"
                              placeholder="Дополнительная информация о ПО, лицензионные ограничения и т.д.">{{ old('notes') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Активно (участвует в проверках)</span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="reset" class="btn btn-secondary">Очистить</button>
                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Сохранить
                </button>
            </div>
        </form>
    </div>
@endsection
