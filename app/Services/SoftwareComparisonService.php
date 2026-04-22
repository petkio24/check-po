<?php
// app/Services/SoftwareComparisonService.php

namespace App\Services;

use App\Models\AllowedSoftware;
use App\Models\ReportItem;
use Illuminate\Support\Collection;

class SoftwareComparisonService
{
    /**
     * Умное сравнение ПО с разрешённым списком
     */
    public function compare($programName, $version, $vendor = null)
    {
        $normalizedName = AllowedSoftware::normalizeName($programName);
        $normalizedVersion = AllowedSoftware::normalizeVersion($version);
        $versionParts = AllowedSoftware::parseVersion($version);

        // Поиск совпадений
        $matches = $this->findMatches($normalizedName, $normalizedVersion, $vendor);

        if ($matches->isEmpty()) {
            return [
                'status' => 'illegitimate',
                'match_type' => 'not_found',
                'match_details' => [
                    'reason' => 'Программа не найдена в разрешённом списке',
                    'suggestions' => $this->findSimilar($normalizedName)
                ]
            ];
        }

        // Проверка точного совпадения версии
        $exactMatch = $matches->first(function ($match) use ($normalizedVersion) {
            return $match->version_normalized === $normalizedVersion;
        });

        if ($exactMatch) {
            return [
                'status' => 'legitimate',
                'match_type' => 'exact',
                'match_details' => [
                    'matched_id' => $exactMatch->id,
                    'matched_name' => $exactMatch->name,
                    'matched_version' => $exactMatch->version,
                    'confidence' => 100
                ]
            ];
        }

        // Проверка на несовпадение версии
        $nameMatch = $matches->first();
        if ($nameMatch) {
            $versionDiff = $this->compareVersions($versionParts, $nameMatch->version_parts);

            return [
                'status' => 'version_mismatch',
                'match_type' => 'version_mismatch',
                'match_details' => [
                    'matched_id' => $nameMatch->id,
                    'matched_name' => $nameMatch->name,
                    'expected_version' => $nameMatch->version,
                    'actual_version' => $version,
                    'version_difference' => $versionDiff,
                    'is_newer' => $this->isNewerVersion($versionParts, $nameMatch->version_parts),
                    'is_older' => $this->isOlderVersion($versionParts, $nameMatch->version_parts),
                    'confidence' => 80
                ]
            ];
        }

        return [
            'status' => 'illegitimate',
            'match_type' => 'not_found',
            'match_details' => ['reason' => 'Совпадение не найдено']
        ];
    }

    /**
     * Поиск совпадений в разрешённом списке
     */
    private function findMatches($normalizedName, $normalizedVersion, $vendor = null)
    {
        $query = AllowedSoftware::where('is_active', true)
            ->where('normalized_name', $normalizedName);

        if ($vendor) {
            $query->where(function ($q) use ($vendor) {
                $q->where('vendor', $vendor)
                    ->orWhereNull('vendor');
            });
        }

        return $query->get();
    }

    /**
     * Поиск похожих программ (для предложений)
     */
    private function findSimilar($normalizedName)
    {
        return AllowedSoftware::where('normalized_name', 'like', '%' . $normalizedName . '%')
            ->orWhere('normalized_name', 'like', $normalizedName . '%')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return $item->name . ' ' . $item->version;
            })
            ->toArray();
    }

    /**
     * Сравнение версий
     */
    private function compareVersions($actual, $expected)
    {
        $diff = [];

        if ($actual['major'] != $expected['major']) {
            $diff['major'] = $actual['major'] - $expected['major'];
        }
        if ($actual['minor'] != $expected['minor']) {
            $diff['minor'] = $actual['minor'] - $expected['minor'];
        }
        if ($actual['patch'] != $expected['patch']) {
            $diff['patch'] = $actual['patch'] - $expected['patch'];
        }

        return $diff;
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
