<?php
// app/Services/ConsoleListParserService.php

namespace App\Services;

class ConsoleListParserService
{
    /**
     * Парсинг списка ПО из консоли
     * Формат: "Название программы    Версия"
     */
    public function parse($content)
    {
        $lines = explode("\n", $content);
        $softwareList = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Пропускаем заголовки и разделители
            if (strpos($line, 'Name') !== false ||
                strpos($line, 'Version') !== false ||
                strpos($line, '---') !== false ||
                strpos($line, '====') !== false) {
                continue;
            }

            // Разделяем по табуляции или нескольким пробелам
            $parts = preg_split('/[\t]{2,}|\s{2,}/', $line);

            if (count($parts) >= 2) {
                $softwareList[] = [
                    'program_name' => trim($parts[0]),
                    'version' => trim($parts[1]),
                    'vendor' => null
                ];
            } elseif (count($parts) == 1 && !empty($parts[0])) {
                // Если только название, без версии
                $softwareList[] = [
                    'program_name' => trim($parts[0]),
                    'version' => '',
                    'vendor' => null
                ];
            }
        }

        return $softwareList;
    }

    /**
     * Парсинг формата: "Программа - Версия"
     */
    public function parseWithDash($content)
    {
        $lines = explode("\n", $content);
        $softwareList = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (strpos($line, ' - ') !== false) {
                $parts = explode(' - ', $line, 2);
                $softwareList[] = [
                    'program_name' => trim($parts[0]),
                    'version' => trim($parts[1] ?? ''),
                    'vendor' => null
                ];
            }
        }

        return $softwareList;
    }

    /**
     * Автоопределение формата и парсинг
     */
    public function autoParse($content)
    {
        // Пробуем разные форматы
        $formats = [
            $this->parse($content),
            $this->parseWithDash($content)
        ];

        // Возвращаем тот, который дал больше результатов
        usort($formats, function($a, $b) {
            return count($b) - count($a);
        });

        return $formats[0];
    }
}
