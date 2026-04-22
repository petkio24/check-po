{{-- resources/views/pc-checks/create.blade.php --}}
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
        <!-- Инструкция -->
        <div class="instruction-panel">
            <div class="instruction-header">
                <span class="instruction-icon">📋</span>
                <strong>Как получить список ПО с ПК</strong>
            </div>
            <div class="instruction-content">
                <div class="method">
                    <div class="method-title">Windows (PowerShell)</div>
                    <code class="method-code">Get-WmiObject -Class Win32_Product | Select-Object Name, Version | Format-Table -AutoSize</code>
                    <div class="method-note">Или более быстрый вариант:</div>
                    <code class="method-code">Get-ItemProperty HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\* | Select-Object DisplayName, DisplayVersion | Where-Object {$_.DisplayName -ne $null} | Format-Table -AutoSize</code>
                </div>
                <div class="method">
                    <div class="method-title">Linux (bash)</div>
                    <code class="method-code">dpkg-query -l | awk '{print $2"    "$3}'</code>
                    <div class="method-note">или</div>
                    <code class="method-code">rpm -qa --queryformat "%{NAME}    %{VERSION}\n"</code>
                </div>
                <div class="method">
                    <div class="method-title">macOS</div>
                    <code class="method-code">system_profiler SPApplicationsDataType | grep -E "Location:|Version:"</code>
                </div>
            </div>
        </div>

        <form action="{{ route('pc-checks.store') }}" method="POST" id="pcCheckForm">
            @csrf

            <!-- Информация о ПК -->
            <div class="form-card">
                <div class="card-title">Информация о компьютере (опционально)</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Имя ПК</label>
                        <input type="text" name="pc_name" class="form-input" placeholder="WORKSTATION-001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">IP-адрес</label>
                        <input type="text" name="pc_ip" class="form-input" placeholder="192.168.1.100">
                    </div>
                </div>
            </div>

            <!-- Формат ввода -->
            <div class="form-card">
                <div class="card-title">Формат списка</div>
                <div class="format-options">
                    <label class="radio-label">
                        <input type="radio" name="format" value="auto" checked>
                        <span>Автоопределение</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="format" value="table">
                        <span>Таблица (название    версия)</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="format" value="dash">
                        <span>С тире (название - версия)</span>
                    </label>
                </div>
            </div>

            <!-- Область ввода списка -->
            <div class="form-card">
                <div class="card-title">Список программ</div>
                <div class="textarea-container">
                <textarea name="software_list"
                          id="softwareList"
                          class="form-textarea software-textarea"
                          placeholder='Вставьте список программ из консоли, например:

7-Zip 23.01
Google Chrome 120.0.6099.130
Microsoft Office 16.0.16026.20200
Adobe Acrobat Reader DC 23.006.20380

Или в формате:
7-Zip - 23.01
Google Chrome - 120.0.6099.130'
                          required></textarea>
                </div>
                <div class="textarea-hint">
                    <span id="lineCount">0</span> строк распознано
                </div>
            </div>

            <!-- Пример -->
            <div class="example-panel">
                <div class="example-header">
                    <span>📄 Пример правильного формата</span>
                    <button type="button" id="loadExample" class="btn-link">Заполнить пример</button>
                </div>
                <div class="example-content">
                <pre>Adobe Acrobat Reader DC    23.006.20380
Google Chrome    120.0.6099.130
Microsoft Visual C++    14.38.33130
7-Zip    23.01
Python    3.11.5
Git    2.42.0
Node.js    20.9.0
Docker Desktop    4.25.0</pre>
                </div>
            </div>

            <!-- Кнопки -->
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
            const textarea = document.getElementById('softwareList');
            const lineCountSpan = document.getElementById('lineCount');
            const clearBtn = document.getElementById('clearForm');
            const loadExampleBtn = document.getElementById('loadExample');
            const submitBtn = document.getElementById('submitBtn');

            function updateLineCount() {
                const lines = textarea.value.split('\n').filter(line => line.trim().length > 0);
                const validLines = lines.filter(line => {
                    // Проверяем, что строка содержит название программы
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

            loadExampleBtn.addEventListener('click', () => {
                textarea.value = `Adobe Acrobat Reader DC    23.006.20380
Google Chrome    120.0.6099.130
Microsoft Visual C++    14.38.33130
7-Zip    23.01
Python    3.11.5
Git    2.42.0
Node.js    20.9.0
Docker Desktop    4.25.0`;
                updateLineCount();
            });

            // Валидация перед отправкой
            document.getElementById('pcCheckForm').addEventListener('submit', (e) => {
                const lines = textarea.value.split('\n').filter(line => line.trim().length > 0);
                if (lines.length === 0) {
                    e.preventDefault();
                    alert('Введите список программ для проверки');
                }
            });

            updateLineCount();
        </script>
    @endpush
@endsection
