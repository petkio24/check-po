<?php
// app/Models/AllowedSoftware.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllowedSoftware extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'version', 'vendor', 'normalized_name',
        'version_normalized', 'version_parts', 'is_active', 'notes'
    ];

    protected $casts = [
        'version_parts' => 'array',
        'is_active' => 'boolean'
    ];

    // Нормализация названия программы
    public static function normalizeName($name)
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/[^\p{L}\p{N}\s\-\.]/u', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        // Удаление распространённых суффиксов
        $suffixes = [' x64', ' x86', ' 64-bit', ' 32-bit', ' (x64)', ' (x86)'];
        foreach ($suffixes as $suffix) {
            $name = str_replace($suffix, '', $name);
        }

        return $name;
    }

    // Парсинг версии на компоненты
    public static function parseVersion($version)
    {
        if (empty($version) || $version === '-') {
            return ['major' => 0, 'minor' => 0, 'patch' => 0, 'build' => 0, 'string' => ''];
        }

        // Извлечение чисел из версии
        preg_match_all('/(\d+)/', $version, $matches);
        $numbers = $matches[1];

        return [
            'major' => $numbers[0] ?? 0,
            'minor' => $numbers[1] ?? 0,
            'patch' => $numbers[2] ?? 0,
            'build' => $numbers[3] ?? 0,
            'string' => $version
        ];
    }

    // Нормализация версии для сравнения
    public static function normalizeVersion($version)
    {
        $parts = self::parseVersion($version);
        return sprintf("%d.%d.%d.%d", $parts['major'], $parts['minor'], $parts['patch'], $parts['build']);
    }
}
