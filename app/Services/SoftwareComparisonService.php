<?php

namespace App\Services;

use App\Models\AllowedSoftware;

class SoftwareComparisonService
{
    /**
     * Умное сравнение ПО с разрешённым списком
     */
    public function compare($programName, $version = null, $vendor = null)
    {
        $programName = trim($programName);

        if (empty($programName)) {
            return [
                'status' => 'illegitimate',
                'match_type' => 'not_found',
                'match_details' => [
                    'reason' => 'Пустое название программы'
                ]
            ];
        }

        $searchResult = AllowedSoftware::smartSearch($programName, $version);
        $matches = $searchResult['matches'];

        \Log::info('Compare result', [
            'program' => $programName,
            'version' => $version,
            'matches_count' => $matches->count(),
            'matches' => $matches->map(fn($m) => $m->name . ' ' . $m->version)->toArray()
        ]);

        if ($matches->isEmpty()) {
            return [
                'status' => 'illegitimate',
                'match_type' => 'not_found',
                'match_details' => [
                    'reason' => 'Программа не найдена в разрешённом списке',
                    'searched_name' => $programName,
                    'searched_version' => $version
                ]
            ];
        }

        $searchVersion = $version ?: $searchResult['extracted_version'];
        $normalizedSearchVersion = !empty($searchVersion) ? AllowedSoftware::normalizeVersion($searchVersion) : null;

        $exactMatch = null;
        $versionMismatchMatch = null;
        $anyMatch = null;

        foreach ($matches as $match) {
            $matchVersionNorm = $match->version_normalized;

            if (!$anyMatch) {
                $anyMatch = $match;
            }

            if (empty($normalizedSearchVersion) || $normalizedSearchVersion === '0.0.0.0') {
                if (empty($matchVersionNorm) || $matchVersionNorm === '0.0.0.0') {
                    $exactMatch = $match;
                    break;
                }
                continue;
            }

            if ($matchVersionNorm === $normalizedSearchVersion) {
                $exactMatch = $match;
                break;
            }

            if (!$versionMismatchMatch && !empty($matchVersionNorm) && $matchVersionNorm !== '0.0.0.0') {
                $versionMismatchMatch = $match;
            }
        }

        if ($exactMatch) {
            return [
                'status' => 'legitimate',
                'match_type' => 'exact',
                'match_details' => [
                    'matched_id' => $exactMatch->id,
                    'matched_name' => $exactMatch->name,
                    'matched_version' => $exactMatch->version ?: 'любая',
                    'confidence' => 100
                ]
            ];
        }

        if ($versionMismatchMatch) {
            return [
                'status' => 'version_mismatch',
                'match_type' => 'version_mismatch',
                'match_details' => [
                    'matched_id' => $versionMismatchMatch->id,
                    'matched_name' => $versionMismatchMatch->name,
                    'expected_version' => $versionMismatchMatch->version ?: 'не указана',
                    'actual_version' => $searchVersion ?: 'не указана',
                    'confidence' => 50
                ]
            ];
        }

        if ($anyMatch) {
            return [
                'status' => 'legitimate',
                'match_type' => 'name_only',
                'match_details' => [
                    'matched_id' => $anyMatch->id,
                    'matched_name' => $anyMatch->name,
                    'matched_version' => $anyMatch->version ?: 'любая',
                    'note' => 'Версия не указана в разрешённом списке',
                    'confidence' => 85
                ]
            ];
        }

        return [
            'status' => 'illegitimate',
            'match_type' => 'not_found',
            'match_details' => [
                'reason' => 'Программа не найдена в разрешённом списке',
                'searched_name' => $programName,
                'searched_version' => $version
            ]
        ];
    }

    /**
     * Проверка, что версия новее разрешённой
     */
    private function isNewerVersion($actual, $expected)
    {
        if ($actual['major'] > $expected['major']) return true;
        if ($actual['major'] == $expected['major'] && $actual['minor'] > $expected['minor']) return true;
        if ($actual['major'] == $expected['major'] && $actual['minor'] == $expected['minor'] && $actual['patch'] > $expected['patch']) return true;
        return false;
    }

    /**
     * Проверка, что версия старше разрешённой
     */
    private function isOlderVersion($actual, $expected)
    {
        if ($actual['major'] < $expected['major']) return true;
        if ($actual['major'] == $expected['major'] && $actual['minor'] < $expected['minor']) return true;
        if ($actual['major'] == $expected['major'] && $actual['minor'] == $expected['minor'] && $actual['patch'] < $expected['patch']) return true;
        return false;
    }
}
