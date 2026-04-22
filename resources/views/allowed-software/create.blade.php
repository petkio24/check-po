{{-- resources/views/allowed-software/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Добавить разрешённое ПО')

@section('content')
    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Добавление программного обеспечения в разрешённый список</h2>
            <p class="mt-1 text-sm text-gray-500">Заполните информацию о программе, которая будет считаться легитимной</p>
        </div>

        <form action="{{ route('allowed-software.store') }}" method="POST" class="p-6">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Название программы *</label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('name') border-red-500 @enderror"
                           value="{{ old('name') }}">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="version" class="block text-sm font-medium text-gray-700">Версия</label>
                    <input type="text" name="version" id="version"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                           value="{{ old('version') }}" placeholder="например: 23.01 (x64)">
                    <p class="mt-1 text-xs text-gray-500">Оставьте пустым, если версия не важна</p>
                    @error('version')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="vendor" class="block text-sm font-medium text-gray-700">Поставщик/Производитель</label>
                    <input type="text" name="vendor" id="vendor"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                           value="{{ old('vendor') }}" placeholder="например: Igor Pavlov">
                    @error('vendor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Примечания</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('notes') }}</textarea>
                    @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('allowed-software.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition">
                    Отмена
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
@endsection
