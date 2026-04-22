<?php
// app/Services/ConsoleListParserService.php

namespace App\Services;

class ConsoleListParserService
{
    /**
     * Парсинг списка ПО из консоли
     */
    public function parse($content)
    {
        $lines = explode("\n", $content);
        $softwareList = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Пропускаем служебные строки
            $skipPatterns = [
                '/^Name/i', '/^Version/i', '/^---/', '/^===/',
                '/^DisplayName/i', '/^DisplayVersion/i', '/^ProductName/i',
                '/^------/', '/^======/', '/^PS /', '/^C:/', '/^$/'
            ];

            $shouldSkip = false;
            foreach ($skipPatterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $shouldSkip = true;
                    break;
                }
            }

            if ($shouldSkip) continue;

            // Разбираем строку
            $parsed = $this->parseLine($line);
            if ($parsed && !empty($parsed['name'])) {
                $softwareList[] = [
                    'program_name' => $parsed['name'],
                    'version' => $parsed['version'],
                    'vendor' => null
                ];
            }
        }

        return $softwareList;
    }

    /**
     * Разбор одной строки
     */
    private function parseLine($line)
    {
        // Способ 1: через табуляцию
        if (strpos($line, "\t") !== false) {
            $parts = explode("\t", $line);
            $parts = array_filter($parts, fn($p) => trim($p) !== '');
            $parts = array_values($parts);

            if (count($parts) >= 1) {
                return [
                    'name' => trim($parts[0]),
                    'version' => trim($parts[1] ?? '')
                ];
            }
        }

        // Способ 2: через 2 и более пробелов
        if (preg_match('/^(.+?)\s{2,}(.+?)$/', $line, $matches)) {
            return [
                'name' => trim($matches[1]),
                'version' => trim($matches[2])
            ];
        }

        // Способ 3: через дефис с пробелами
        if (strpos($line, ' - ') !== false) {
            $parts = explode(' - ', $line, 2);
            return [
                'name' => trim($parts[0]),
                'version' => trim($parts[1] ?? '')
            ];
        }

        // Способ 4: версия в конце через пробел (версия похожа на числа с точками)
        if (preg_match('/^(.+?)\s+([\d\.]+(?:\s*\([^)]+\))?(?:\s+[\d\.]+)*)$/', $line, $matches)) {
            return [
                'name' => trim($matches[1]),
                'version' => trim($matches[2])
            ];
        }

        // Способ 5: версия в конце через пробел (версия начинается с буквы v)
        if (preg_match('/^(.+?)\s+(v[\d\.]+.*)$/i', $line, $matches)) {
            return [
                'name' => trim($matches[1]),
                'version' => trim($matches[2])
            ];
        }

        // Способ 6: только название
        if (!empty($line) && strlen($line) < 100) {
            return [
                'name' => trim($line),
                'version' => ''
            ];
        }

        return null;
    }

    /**
     * Автоопределение формата
     */
    public function autoParse($content)
    {
        return $this->parse($content);
    }
}
