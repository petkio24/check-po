@extends('layouts.app')

@section('title', 'Проверка ПК')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Проверка легитимности ПО на ПК</h1>
            <p class="page-subtitle">Вставьте список программ из консоли для проверки</p>
        </div>
        <a href="{{ route('pc-checks.index') }}" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            История проверок
        </a>
    </div>

    <div class="pc-check-creation">
        <form action="{{ route('pc-checks.store') }}" method="POST" id="pcCheckForm">
            @csrf

            <div class="form-card">
                <div class="card-title">Информация о проверке</div>

                <div class="form-group">
                    <label class="form-label required">Название проверки</label>
                    <input type="text" name="check_name" id="checkName" class="form-input @error('check_name') is-invalid @enderror"
                           value="{{ old('check_name') }}" required>
                    @error('check_name')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Автоматически заполняется текущей датой, можно отредактировать</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Имя ПК</label>
                        <input type="text" name="pc_name" class="form-input" value="{{ old('pc_name') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">IP-адрес</label>
                        <input type="text" name="pc_ip" class="form-input" value="{{ old('pc_ip') }}">
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="card-title">Список программ</div>

                <div class="info-note">
                    <div class="info-note-text">
                        Формат списка определяется автоматически. Поддерживаются форматы: <strong>Название    Версия</strong> (через пробелы или табуляцию) или <strong>Название - Версия</strong>
                    </div>
                </div>

                <textarea name="software_list"
                          id="softwareList"
                          class="form-textarea software-textarea"
                          required></textarea>
                <div class="textarea-hint">
                    <span id="lineCount">0</span> строк распознано
                </div>
            </div>

            <div class="form-actions">
                <button type="button" id="clearForm" class="btn btn-secondary">Очистить</button>
                <button type="submit" class="btn btn-primary btn-large" id="submitBtn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                    Проверить
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Автозаполнение названия проверки
            const checkNameInput = document.getElementById('checkName');
            const today = new Date();
            const defaultName = `Проверка от ${today.getDate().toString().padStart(2, '0')}.${(today.getMonth() + 1).toString().padStart(2, '0')}.${today.getFullYear()} ${today.getHours().toString().padStart(2, '0')}:${today.getMinutes().toString().padStart(2, '0')}`;

            if (!checkNameInput.value) {
                checkNameInput.value = defaultName;
            }

            const textarea = document.getElementById('softwareList');
            const lineCountSpan = document.getElementById('lineCount');
            const clearBtn = document.getElementById('clearForm');

            function updateLineCount() {
                const lines = textarea.value.split('\n').filter(line => line.trim().length > 0);
                const validLines = lines.filter(line => {
                    return line.trim().length > 0 && !line.includes('---') && !line.includes('Name');
                });
                lineCountSpan.textContent = validLines.length;
            }

            textarea.addEventListener('input', updateLineCount);
            textarea.addEventListener('paste', () => setTimeout(updateLineCount, 10));

            clearBtn.addEventListener('click', () => {
                textarea.value = '';
                updateLineCount();
            });

            updateLineCount();
        </script>
    @endpush
@endsection
