<?php
// app/Http/Controllers/AllowedSoftwareController.php

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

    public function index()
    {
        $software = AllowedSoftware::orderBy('name')->orderBy('version')->paginate(50);
        return view('allowed-software.index', compact('software'));
    }

    public function create()
    {
        return view('allowed-software.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $validated['normalized_name'] = AllowedSoftware::normalizeName($validated['name']);
        $validated['version_normalized'] = AllowedSoftware::normalizeVersion($validated['version'] ?? '');
        $validated['version_parts'] = AllowedSoftware::parseVersion($validated['version'] ?? '');
        $validated['is_active'] = $request->has('is_active');

        AllowedSoftware::create($validated);

        return redirect()->route('allowed-software.index')
            ->with('success', 'ПО добавлено в разрешённый список');
    }

    public function edit(AllowedSoftware $allowedSoftware)
    {
        return view('allowed-software.edit', compact('allowedSoftware'));
    }

    public function update(Request $request, AllowedSoftware $allowedSoftware)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $validated['normalized_name'] = AllowedSoftware::normalizeName($validated['name']);
        $validated['version_normalized'] = AllowedSoftware::normalizeVersion($validated['version'] ?? '');
        $validated['version_parts'] = AllowedSoftware::parseVersion($validated['version'] ?? '');
        $validated['is_active'] = $request->has('is_active');

        $allowedSoftware->update($validated);

        return redirect()->route('allowed-software.index')
            ->with('success', 'ПО обновлено');
    }

    public function destroy(AllowedSoftware $allowedSoftware)
    {
        $allowedSoftware->delete();

        return redirect()->route('allowed-software.index')
            ->with('success', 'ПО удалено из списка');
    }

    public function checkSimilar(Request $request)
    {
        $normalizedName = AllowedSoftware::normalizeName($request->get('name'));
        $similar = AllowedSoftware::where('normalized_name', 'like', '%' . $normalizedName . '%')
            ->limit(10)
            ->get(['id', 'name', 'version', 'vendor']);

        return response()->json($similar);
    }
}
