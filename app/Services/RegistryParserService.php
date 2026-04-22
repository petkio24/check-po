<?php

namespace App\Services;

use App\Models\AllowedSoftware;

class RegistryParserService
{
    /**
     * Парсинг файла реестра из DOCX
     */
    public function parse($fileContent)
    {
        $lines = explode("\n", $fileContent);
        $softwareList = [];

        $inTable = false;

        foreach ($lines as $line) {
            if (strpos($line, 'Программа') !== false || strpos($line, '--------') !== false) {
                $inTable = true;
                continue;
            }

            if (!$inTable) continue;

            $parsed = $this->parseTableRow($line);
            if ($parsed) {
                $softwareList[] = $parsed;
            }
        }

        return $softwareList;
    }

    /**
     * Парсинг строки таблицы
     */
    private function parseTableRow($line)
    {
        $parts = preg_split('/\s{2,}/', trim($line));

        if (count($parts) < 3) return null;

        $programName = $parts[0];
        $version = $parts[1] ?? '-';
        $vendor = $parts[2] ?? 'Неизвестно';
        $devicesCount = 1;

        if (isset($parts[3])) {
            $devicesCount = intval(preg_replace('/[^0-9]/', '', $parts[3]));
            if ($devicesCount == 0) $devicesCount = 1;
        }

        $programName = trim($programName);
        $version = trim($version);
        $vendor = trim($vendor);

        if (empty($programName) || $programName === '-') return null;

        return [
            'program_name' => $programName,
            'version' => $version,
            'vendor' => $vendor,
            'devices_count' => $devicesCount
        ];
    }
}
