<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportItem;
use App\Models\AllowedSoftware;
use App\Services\RegistryParserService;
use App\Services\SoftwareComparisonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $parser;
    protected $comparisonService;

    public function __construct(RegistryParserService $parser, SoftwareComparisonService $comparisonService)
    {
        $this->parser = $parser;
        $this->comparisonService = $comparisonService;
    }

    public function index()
    {
        $reports = Report::orderBy('created_at', 'desc')->paginate(20);
        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        return view('reports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'registry_file' => 'required|file|mimes:docx,txt|max:10240'
        ]);

        $file = $request->file('registry_file');
        $content = file_get_contents($file->getPathname());

        // Парсинг файла
        $softwareList = $this->parser->parse($content);

        // Создание отчёта
        $report = Report::create([
            'file_name' => $file->getClientOriginalName(),
            'total_entries' => count($softwareList),
            'legitimate_count' => 0,
            'illegitimate_count' => 0,
            'version_mismatch_count' => 0
        ]);

        // Сравнение каждого ПО
        $legitimate = 0;
        $illegitimate = 0;
        $versionMismatch = 0;

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

            ReportItem::create([
                'report_id' => $report->id,
                'program_name' => $software['program_name'],
                'version' => $software['version'],
                'vendor' => $software['vendor'],
                'devices_count' => $software['devices_count'],
                'normalized_name' => AllowedSoftware::normalizeName($software['program_name']),
                'version_normalized' => AllowedSoftware::normalizeVersion($software['version']),
                'status' => $status,
                'matched_allowed_id' => $matchedId,
                'match_type' => $matchType,
                'match_details' => $comparison['match_details']
            ]);
        }

        // Обновление статистики отчёта
        $report->update([
            'legitimate_count' => $legitimate,
            'illegitimate_count' => $illegitimate,
            'version_mismatch_count' => $versionMismatch,
            'summary' => [
                'total_devices' => collect($softwareList)->sum('devices_count'),
                'legitimate_devices' => $legitimate,
                'illegitimate_devices' => $illegitimate
            ]
        ]);

        return redirect()->route('reports.show', $report)
            ->with('success', 'Отчёт успешно создан');
    }

    public function show(Report $report)
    {
        $items = $report->items()->with('matchedAllowed')->paginate(50);

        $stats = [
            'legitimate' => $report->legitimate_count,
            'illegitimate' => $report->illegitimate_count,
            'version_mismatch' => $report->version_mismatch_count
        ];

        return view('reports.show', compact('report', 'items', 'stats'));
    }

    public function export(Report $report)
    {
        $items = $report->items()->get();

        $csv = "Программа,Версия,Поставщик,Кол-во устройств,Статус,Детали\n";

        foreach ($items as $item) {
            $status = $item->status === 'legitimate' ? '✓ Разрешено' :
                ($item->status === 'version_mismatch' ? '⚠ Несовпадение версии' : '✗ Не разрешено');

            $details = '';
            if ($item->match_type === 'version_mismatch') {
                $details = "Ожидалось: " . ($item->match_details['expected_version'] ?? '');
            } elseif ($item->match_type === 'not_found') {
                $details = $item->match_details['reason'] ?? '';
            }

            $csv .= sprintf(
                "\"%s\",\"%s\",\"%s\",%d,\"%s\",\"%s\"\n",
                $item->program_name,
                $item->version,
                $item->vendor ?? '-',
                $item->devices_count,
                $status,
                $details
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=report_{$report->id}.csv");
    }

    public function destroy(Report $report)
    {
        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Отчёт удалён');
    }
}
