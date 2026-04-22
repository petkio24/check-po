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

        // Удаляем версию из названия (если есть)
        $name = preg_replace('/\s+[\d\.]+.*$/', '', $name);
        $name = preg_replace('/\s+v\d+.*$/i', '', $name);
        $name = preg_replace('/\s+version\s+[\d\.]+.*$/i', '', $name);

        // Удаляем распространённые суффиксы
        $suffixes = [
            ' x64', ' x86', ' 64-bit', ' 32-bit', ' (x64)', ' (x86)',
            ' for windows', ' for mac', ' for linux', ' для windows',
            ' professional', ' enterprise', ' standard', ' ultimate',
            ' free', ' pro', ' lite', ' basic'
        ];
        foreach ($suffixes as $suffix) {
            $name = str_replace($suffix, '', $name);
        }

        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);

        return $name;
    }

    // Извлечение версии из названия программы
    public static function extractVersionFromName($name)
    {
        $patterns = [
            '/(\d+\.\d+\.\d+\.\d+)/',
            '/(\d+\.\d+\.\d+)/',
            '/(\d+\.\d+)/',
            '/v(\d+\.\d+\.\d+)/i',
            '/version\s+(\d+\.\d+)/i',
            '/\((\d+\.\d+\.\d+)\)/',
            '/\[(\d+\.\d+\.\d+)\]/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $name, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    // Умное разделение названия и версии
    public static function splitNameAndVersion($fullName)
    {
        $version = self::extractVersionFromName($fullName);

        if ($version) {
            $name = preg_replace('/\s*' . preg_quote($version, '/') . '\s*/', '', $fullName);
            $name = preg_replace('/\s*v' . preg_quote($version, '/') . '\s*/i', '', $name);
            $name = preg_replace('/\s*version\s*' . preg_quote($version, '/') . '\s*/i', '', $name);
            $name = preg_replace('/\s*\(?' . preg_quote($version, '/') . '\)?\s*/', '', $name);
            $name = preg_replace('/\s*\[?' . preg_quote($version, '/') . '\]?\s*/', '', $name);
            $name = trim($name);
        } else {
            $name = $fullName;
        }

        return [
            'name' => $name,
            'version' => $version
        ];
    }

    // Парсинг версии на компоненты
    public static function parseVersion($version)
    {
        if (empty($version) || $version === '-') {
            return ['major' => 0, 'minor' => 0, 'patch' => 0, 'build' => 0, 'string' => ''];
        }

        preg_match_all('/(\d+)/', $version, $matches);
        $numbers = $matches[1];

        return [
            'major' => intval($numbers[0] ?? 0),
            'minor' => intval($numbers[1] ?? 0),
            'patch' => intval($numbers[2] ?? 0),
            'build' => intval($numbers[3] ?? 0),
            'string' => $version
        ];
    }

    // Нормализация версии для сравнения
    public static function normalizeVersion($version)
    {
        $parts = self::parseVersion($version);
        return sprintf("%d.%d.%d.%d", $parts['major'], $parts['minor'], $parts['patch'], $parts['build']);
    }

    /**
     * Умный поиск ПО по названию (с учётом возможной версии в названии)
     */
    public static function smartSearch($programName, $version = null)
    {
        // Очищаем название от мусора
        $cleanProgramName = trim($programName);

        // Разделяем название и версию из названия
        $split = self::splitNameAndVersion($cleanProgramName);
        $cleanName = $split['name'];
        $extractedVersion = $split['version'];

        // Используем переданную версию или извлечённую
        $searchVersion = $version ?: $extractedVersion;

        // Нормализуем название для поиска
        $normalizedName = self::normalizeName($cleanName);

        // Логируем поиск (для отладки)
        \Log::info('Smart search', [
            'original' => $programName,
            'clean_name' => $cleanName,
            'normalized' => $normalizedName,
            'version' => $searchVersion
        ]);

        // Поиск по точному совпадению нормализованного названия
        $results = self::where('is_active', true)
            ->where('normalized_name', $normalizedName)
            ->get();

        // Если ничего не найдено - поиск по частичному совпадению
        if ($results->isEmpty()) {
            $results = self::where('is_active', true)
                ->where('normalized_name', 'like', '%' . $normalizedName . '%')
                ->get();
        }

        // Если всё ещё ничего не найдено - поиск по имени без нормализации
        if ($results->isEmpty()) {
            $results = self::where('is_active', true)
                ->where('name', 'like', '%' . $cleanName . '%')
                ->get();
        }

        return [
            'matches' => $results,
            'clean_name' => $cleanName,
            'extracted_version' => $extractedVersion,
            'search_version' => $searchVersion,
            'normalized_name' => $normalizedName
        ];
    }

    /**
     * Поиск похожих программ
     */
    public static function findSimilar($programName)
    {
        $normalizedName = self::normalizeName($programName);

        return self::where('is_active', true)
            ->where('normalized_name', 'like', '%' . $normalizedName . '%')
            ->limit(5)
            ->get();
    }
}
