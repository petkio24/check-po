{{-- resources/views/reports/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Новая проверка')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Новая проверка легитимности</h1>
            <p class="page-subtitle">Загрузите файл реестра для автоматического анализа ПО</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Назад к отчётам
        </a>
    </div>

    <div class="report-creation">
        <!-- Инструкция -->
        <div class="info-panel">
            <div class="info-icon">ℹ️</div>
            <div class="info-content">
                <strong>Требования к файлу реестра</strong>
                <p>Файл должен быть в формате DOCX или TXT, содержать таблицу с колонками: Программа, Версия, Поставщик, Количество устройств</p>
            </div>
        </div>

        <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" id="reportForm">
            @csrf

            <!-- Область загрузки -->
            <div class="upload-container" id="uploadContainer">
                <input type="file" name="registry_file" id="fileInput" class="file-input-hidden" accept=".docx,.txt,.doc">
                <div class="upload-area" id="uploadArea">
                    <div class="upload-preview" id="uploadPreview" style="display: none;">
                        <div class="preview-icon">📄</div>
                        <div class="preview-name" id="fileName"></div>
                        <div class="preview-size" id="fileSize"></div>
                        <button type="button" class="remove-file" id="removeFile">×</button>
                    </div>
                    <div class="upload-placeholder" id="uploadPlaceholder">
                        <div class="upload-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 5 17 10"/>
                                <line x1="12" y1="5" x2="12" y2="15"/>
                            </svg>
                        </div>
                        <div class="upload-title">Перетащите файл сюда или нажмите для выбора</div>
                        <div class="upload-hint">Поддерживаются форматы: DOCX, TXT (до 10 МБ)</div>
                    </div>
                </div>
                @error('registry_file')
                <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Параметры проверки -->
            <div class="analysis-options">
                <h3 class="options-title">Параметры проверки</h3>
                <div class="options-grid">
                    <label class="option-item">
                        <input type="checkbox" name="strict_version" value="1" checked>
                        <span>Строгое соответствие версий</span>
                    </label>
                    <label class="option-item">
                        <input type="checkbox" name="check_vendor" value="1" checked>
                        <span>Проверять производителя</span>
                    </label>
                    <label class="option-item">
                        <input type="checkbox" name="case_sensitive" value="1">
                        <span>Учитывать регистр</span>
                    </label>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="clearForm">Очистить</button>
                <button type="submit" class="btn btn-primary btn-large" id="submitBtn" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                    Запустить проверку
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('fileInput');
            const uploadPlaceholder = document.getElementById('uploadPlaceholder');
            const uploadPreview = document.getElementById('uploadPreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const removeFile = document.getElementById('removeFile');
            const submitBtn = document.getElementById('submitBtn');
            const clearForm = document.getElementById('clearForm');

            let selectedFile = null;

            uploadArea.addEventListener('click', () => fileInput.click());

            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('drag-over');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('drag-over');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('drag-over');
                const file = e.dataTransfer.files[0];
                if (file) handleFileSelect(file);
            });

            fileInput.addEventListener('change', (e) => {
                if (e.target.files[0]) handleFileSelect(e.target.files[0]);
            });

            function handleFileSelect(file) {
                const validTypes = ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
                const maxSize = 10 * 1024 * 1024;

                if (!validTypes.includes(file.type) && !file.name.endsWith('.docx') && !file.name.endsWith('.txt')) {
                    alert('Неподдерживаемый формат файла');
                    return;
                }

                if (file.size > maxSize) {
                    alert('Файл слишком большой (макс. 10 МБ)');
                    return;
                }

                selectedFile = file;
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                uploadPlaceholder.style.display = 'none';
                uploadPreview.style.display = 'flex';
                submitBtn.disabled = false;
            }

            removeFile.addEventListener('click', (e) => {
                e.stopPropagation();
                selectedFile = null;
                fileInput.value = '';
                uploadPlaceholder.style.display = 'flex';
                uploadPreview.style.display = 'none';
                submitBtn.disabled = true;
            });

            clearForm.addEventListener('click', () => {
                removeFile.click();
                document.querySelectorAll('.analysis-options input').forEach(cb => cb.checked = false);
                document.querySelector('input[name="strict_version"]').checked = true;
                document.querySelector('input[name="check_vendor"]').checked = true;
            });

            function formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' Б';
                if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' КБ';
                return (bytes / 1048576).toFixed(1) + ' МБ';
            }
        </script>
    @endpush
@endsection
