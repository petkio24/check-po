<?php

namespace App\Http\Controllers;

use App\Models\AllowedSoftware;
use App\Services\SoftwareComparisonService;
use Illuminate\Http\Request;

class AllowedSoftwareController extends Controller
{
    protected $comparisonService;

    public function __construct(SoftwareComparisonService $comparisonService)
    {
        $this->comparisonService = $comparisonService;
    }

    /**
     * Список разрешённого ПО
     */
    public function index()
    {
        $software = AllowedSoftware::orderBy('name')
            ->orderBy('version')
            ->paginate(50);

        return view('allowed-software.index', compact('software'));
    }

    /**
     * Форма создания ПО
     */
    public function create()
    {
        return view('allowed-software.create');
    }

    /**
     * Сохранение нового ПО
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $split = AllowedSoftware::splitNameAndVersion($validated['name']);

        if (empty($validated['version']) && $split['version']) {
            $validated['version'] = $split['version'];
            $validated['name'] = $split['name'];
        }

        $validated['normalized_name'] = AllowedSoftware::normalizeName($validated['name']);
        $validated['version_normalized'] = AllowedSoftware::normalizeVersion($validated['version'] ?? '');
        $validated['version_parts'] = AllowedSoftware::parseVersion($validated['version'] ?? '');
        $validated['is_active'] = $request->has('is_active');

        $exists = AllowedSoftware::where('normalized_name', $validated['normalized_name'])
            ->where('version_normalized', $validated['version_normalized'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Такое ПО уже существует в разрешённом списке');
        }

        AllowedSoftware::create($validated);

        return redirect()
            ->route('allowed-software.index')
            ->with('success', 'ПО добавлено в разрешённый список');
    }

    /**
     * Форма редактирования ПО
     */
    public function edit(AllowedSoftware $allowedSoftware)
    {
        return view('allowed-software.edit', compact('allowedSoftware'));
    }

    /**
     * Обновление ПО
     */
    public function update(Request $request, AllowedSoftware $allowedSoftware)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $split = AllowedSoftware::splitNameAndVersion($validated['name']);

        if (empty($validated['version']) && $split['version']) {
            $validated['version'] = $split['version'];
            $validated['name'] = $split['name'];
        }

        $validated['normalized_name'] = AllowedSoftware::normalizeName($validated['name']);
        $validated['version_normalized'] = AllowedSoftware::normalizeVersion($validated['version'] ?? '');
        $validated['version_parts'] = AllowedSoftware::parseVersion($validated['version'] ?? '');
        $validated['is_active'] = $request->has('is_active');

        $allowedSoftware->update($validated);

        return redirect()
            ->route('allowed-software.index')
            ->with('success', 'ПО обновлено');
    }

    /**
     * Удаление ПО
     */
    public function destroy(AllowedSoftware $allowedSoftware)
    {
        $allowedSoftware->delete();

        return redirect()
            ->route('allowed-software.index')
            ->with('success', 'ПО удалено из списка');
    }

    /**
     * Проверка похожих названий (для AJAX)
     */
    public function checkSimilar(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $normalizedName = AllowedSoftware::normalizeName($request->get('name'));

        $similar = AllowedSoftware::where('normalized_name', 'like', '%' . $normalizedName . '%')
            ->orWhere('normalized_name', 'like', $normalizedName . '%')
            ->limit(10)
            ->get(['id', 'name', 'version', 'vendor']);

        return response()->json([
            'similar' => $similar,
            'count' => $similar->count()
        ]);
    }

    /**
     * Умный поиск ПО (для AJAX)
     */
    public function smartSearch(Request $request)
    {
        $request->validate([
            'query' => 'required|string'
        ]);

        $searchResult = AllowedSoftware::smartSearch($request->get('query'));

        return response()->json([
            'matches' => $searchResult['matches'],
            'clean_name' => $searchResult['clean_name'],
            'extracted_version' => $searchResult['extracted_version'],
            'suggestions' => $searchResult['matches']->isEmpty()
                ? $this->findSuggestions($searchResult['normalized_name'])
                : []
        ]);
    }

    /**
     * Поиск предложений для похожих программ
     */
    private function findSuggestions($normalizedName)
    {
        return AllowedSoftware::where('normalized_name', 'like', '%' . $normalizedName . '%')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'version' => $item->version,
                    'vendor' => $item->vendor
                ];
            });
    }

    /**
     * Массовый импорт из файла
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,csv|max:5120'
        ]);

        $content = file_get_contents($request->file('file')->getPathname());
        $lines = explode("\n", $content);
        $added = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = preg_split('/[\t,;]+/', $line);
            $name = $parts[0] ?? '';
            $version = $parts[1] ?? '';
            $vendor = $parts[2] ?? null;

            if (empty($name)) continue;

            $split = AllowedSoftware::splitNameAndVersion($name);
            if (empty($version) && $split['version']) {
                $version = $split['version'];
                $name = $split['name'];
            }

            $normalizedName = AllowedSoftware::normalizeName($name);
            $normalizedVersion = AllowedSoftware::normalizeVersion($version);

            $exists = AllowedSoftware::where('normalized_name', $normalizedName)
                ->where('version_normalized', $normalizedVersion)
                ->exists();

            if (!$exists) {
                AllowedSoftware::create([
                    'name' => $name,
                    'version' => $version,
                    'vendor' => $vendor,
                    'normalized_name' => $normalizedName,
                    'version_normalized' => $normalizedVersion,
                    'version_parts' => AllowedSoftware::parseVersion($version),
                    'is_active' => true
                ]);
                $added++;
            } else {
                $skipped++;
            }
        }

        return redirect()
            ->route('allowed-software.index')
            ->with('success', "Импорт завершён. Добавлено: {$added}, пропущено (дубликаты): {$skipped}");
    }

    /**
     * Экспорт в CSV
     */
    public function export()
    {
        $software = AllowedSoftware::all();

        $headers = ['ID', 'Название', 'Версия', 'Производитель', 'Статус', 'Дата создания'];

        $rows = [];
        foreach ($software as $item) {
            $rows[] = [
                $item->id,
                $item->name,
                $item->version ?: '—',
                $item->vendor ?: '—',
                $item->is_active ? 'Активен' : 'Неактивен',
                $item->created_at->format('d.m.Y H:i')
            ];
        }

        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers, ';', '"', '\\');

        foreach ($rows as $row) {
            fputcsv($output, $row, ';', '"', '\\');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"allowed_software_export_" . date('Ymd_His') . ".csv\"",
        ]);
    }
}
