<?php

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

        if (preg_match('/^(.+?)\s{2,}(.+?)$/', $line, $matches)) {
            return [
                'name' => trim($matches[1]),
                'version' => trim($matches[2])
            ];
        }

        if (strpos($line, ' - ') !== false) {
            $parts = explode(' - ', $line, 2);
            return [
                'name' => trim($parts[0]),
                'version' => trim($parts[1] ?? '')
            ];
        }

        if (preg_match('/^(.+?)\s+([\d\.]+(?:\s*\([^)]+\))?(?:\s+[\d\.]+)*)$/', $line, $matches)) {
            return [
                'name' => trim($matches[1]),
                'version' => trim($matches[2])
            ];
        }

        if (preg_match('/^(.+?)\s+(v[\d\.]+.*)$/i', $line, $matches)) {
            return [
                'name' => trim($matches[1]),
                'version' => trim($matches[2])
            ];
        }

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
