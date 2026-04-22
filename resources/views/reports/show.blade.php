{{-- resources/views/reports/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Отчёт #' . $report->id)

@section('content')
    <div class="space-y-6">
        <!-- Статистика -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">Всего записей</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $report->total_entries }}</dd>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">✓ Разрешённое ПО</dt>
                    <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $stats['legitimate'] }}</dd>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">⚠ Несовпадение версий</dt>
                    <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $stats['version_mismatch'] }}</dd>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">✗ Неразрешённое ПО</dt>
                    <dd class="mt-1 text-3xl font-semibold text-red-600">{{ $stats['illegitimate'] }}</dd>
                </div>
            </div>
        </div>

        <!-- Кнопки действий -->
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900">Детали отчёта</h2>
            <div class="space-x-2">
                <a href="{{ route('reports.export', $report) }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    📥 Экспорт CSV
                </a>
                <form action="{{ route('reports.destroy', $report) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition" onclick="return confirm('Удалить этот отчёт?')">
                        🗑 Удалить
                    </button>
                </form>
            </div>
        </div>

        <!-- Таблица с результатами -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Программа</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Версия</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Поставщик</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Кол-во</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Детали</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($items as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item->program_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item->version }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item->vendor ?: '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item->devices_count }}</td>
                            <td class="px-6 py-4">
                                @if($item->status === 'legitimate')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">✓ Разрешено</span>
                                @elseif($item->status === 'version_mismatch')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">⚠ Несовпадение версии</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">✗ Не разрешено</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($item->match_type === 'version_mismatch')
                                    <span class="text-xs">Ожидалось: {{ $item->match_details['expected_version'] ?? '-' }}</span>
                                @elseif($item->match_type === 'not_found')
                                    <span class="text-xs">{{ $item->match_details['reason'] ?? 'Не найдено' }}</span>
                                @else
                                    <span class="text-xs text-green-600">✓ Совпадает</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection
