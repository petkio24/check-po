{{-- resources/views/reports/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Отчёты')

@section('content')
    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Все отчёты проверки ПО</h2>
            <a href="{{ route('reports.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                + Создать отчёт
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата создания</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Файл</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Всего записей</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Разрешено</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Несовпадение версий</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Не разрешено</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Действия</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($reports as $report)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">#{{ $report->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $report->created_at->format('d.m.Y H:i:s') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $report->file_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $report->total_entries }}</td>
                        <td class="px-6 py-4 text-sm text-green-600 font-medium">{{ $report->legitimate_count }}</td>
                        <td class="px-6 py-4 text-sm text-yellow-600 font-medium">{{ $report->version_mismatch_count }}</td>
                        <td class="px-6 py-4 text-sm text-red-600 font-medium">{{ $report->illegitimate_count }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('reports.show', $report) }}" class="text-blue-600 hover:text-blue-900">Просмотр</a>
                            <a href="{{ route('reports.export', $report) }}" class="text-green-600 hover:text-green-900">Экспорт</a>
                            <form action="{{ route('reports.destroy', $report) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Удалить этот отчёт?')">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">Нет созданных отчётов</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-gray-200">
            {{ $reports->links() }}
        </div>
    </div>
@endsection
