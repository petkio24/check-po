<?php
// app/Http/Controllers/PcCheckController.php

namespace App\Http\Controllers;

use App\Models\PcCheck;
use App\Models\PcCheckItem;
use App\Models\AllowedSoftware;
use App\Services\ConsoleListParserService;
use App\Services\SoftwareComparisonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PcCheckController extends Controller
{
    protected $parser;
    protected $comparisonService;

    public function __construct(
        ConsoleListParserService $parser,
        SoftwareComparisonService $comparisonService
    ) {
        $this->parser = $parser;
        $this->comparisonService = $comparisonService;
    }

    public function index()
    {
        $checks = PcCheck::orderBy('created_at', 'desc')->paginate(20);
        return view('pc-checks.index', compact('checks'));
    }

    public function create()
    {
        return view('pc-checks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'check_name' => 'required|string|max:255',
            'software_list' => 'required|string',
            'pc_name' => 'nullable|string|max:255',
            'pc_ip' => 'nullable|ip',
            'format' => 'nullable|in:auto,table,dash'
        ]);

        $format = $request->get('format', 'auto');
        $content = $request->get('software_list');

        // Парсинг списка
        if ($format === 'auto') {
            $softwareList = $this->parser->autoParse($content);
        } elseif ($format === 'dash') {
            $softwareList = $this->parser->parseWithDash($content);
        } else {
            $softwareList = $this->parser->parse($content);
        }

        if (empty($softwareList)) {
            return back()->with('error', 'Не удалось распознать список ПО. Проверьте формат.');
        }

        // Создание записи проверки
        $pcCheck = PcCheck::create([
            'check_name' => $request->get('check_name'),
            'pc_name' => $request->get('pc_name'),
            'pc_ip' => $request->get('pc_ip'),
            'check_file_name' => 'Ручной ввод',
            'total_software' => count($softwareList),
            'legitimate_count' => 0,
            'illegitimate_count'  => 0,
            'version_mismatch_count' => 0
        ]);

        // Проверка каждого ПО
        $legitimate = 0;
        $illegitimate = 0;
        $versionMismatch = 0;
        $results = [];

        foreach ($softwareList as $software) {
            $comparison = $this->comparisonService->compare(
                $software['program_name'],
                $software['version'],
                $software['vendor']
            );

            $status = $comparison['status'];
            $matchType = $comparison['match_type'];
            $matchedId = $comparison['match_details']['matched_id'] ?? null;

            if ($status === 'legitimate') {
                $legitimate++;
            } elseif ($status === 'version_mismatch') {
                $versionMismatch++;
            } else {
                $illegitimate++;
            }

            PcCheckItem::create([
                'pc_check_id' => $pcCheck->id,
                'program_name' => $software['program_name'],
                'version' => $software['version'],
                'vendor' => $software['vendor'],
                'normalized_name' => AllowedSoftware::normalizeName($software['program_name']),
                'version_normalized' => AllowedSoftware::normalizeVersion($software['version']),
                'status' => $status,
                'matched_allowed_id' => $matchedId,
                'match_details' => json_encode($comparison['match_details'], JSON_UNESCAPED_UNICODE)
            ]);
        }

        // Обновление статистики
        $pcCheck->update([
            'legitimate_count' => $legitimate,
            'illegitimate_count' => $illegitimate,
            'version_mismatch_count' => $versionMismatch,
            'results' => [
                'total' => count($softwareList),
                'legitimate' => $legitimate,
                'illegitimate' => $illegitimate,
                'version_mismatch' => $versionMismatch
            ]
        ]);

        return redirect()->route('pc-checks.show', $pcCheck)
            ->with('success', 'Проверка ПК завершена');
    }

    public function show(PcCheck $pcCheck)
    {
        $items = $pcCheck->items()->with('matchedAllowed')->paginate(50);

        $stats = [
            'legitimate' => $pcCheck->legitimate_count,
            'illegitimate' => $pcCheck->illegitimate_count,
            'version_mismatch' => $pcCheck->version_mismatch_count,
            'compliance_percent' => $pcCheck->total_software > 0
                ? round(($pcCheck->legitimate_count / $pcCheck->total_software) * 100, 1)
                : 0
        ];

        return view('pc-checks.show', compact('pcCheck', 'items', 'stats'));
    }

    public function destroy(PcCheck $pcCheck)
    {
        $pcCheck->delete();
        return redirect()->route('pc-checks.index')
            ->with('success', 'Проверка удалена');
    }

    public function export(PcCheck $pcCheck)
    {
        $items = $pcCheck->items()->get();

        $csv = "Программа,Версия,Поставщик,Статус,Детали\n";

        foreach ($items as $item) {
            $status = $item->status === 'legitimate' ? 'Разрешено' :
                ($item->status === 'version_mismatch' ? 'Несовпадение версии' : 'Не разрешено');

            $details = '';
            $matchDetails = json_decode($item->match_details, true);
            if ($item->status === 'version_mismatch' && isset($matchDetails['expected_version'])) {
                $details = "Ожидалась версия: {$matchDetails['expected_version']}";
            } elseif ($item->status === 'illegitimate' && isset($matchDetails['reason'])) {
                $details = $matchDetails['reason'];
            }

            $csv .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                str_replace('"', '""', $item->program_name),
                str_replace('"', '""', $item->version),
                str_replace('"', '""', $item->vendor ?? '-'),
                $status,
                str_replace('"', '""', $details)
            );
        }

        $filename = "pc_check_{$pcCheck->id}_{$pcCheck->created_at->format('Ymd_His')}.csv";

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
