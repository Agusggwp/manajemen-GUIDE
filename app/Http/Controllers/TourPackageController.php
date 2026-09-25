<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\TourPackage;
use App\Models\TourPackagePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TourPackageController extends Controller
{
    public function index()
    {
        $packages = TourPackage::with('prices')->get();
        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        return view('packages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'duration' => 'required|string|max:50',
            'default_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'has_special_tiers' => 'nullable',
            'tiers' => 'nullable|array',
            'tiers.*.min' => 'required_with:tiers|integer|min:1',
            'tiers.*.max' => 'required_with:tiers|integer|min:1',
            'tiers.*.price' => 'required_with:tiers|numeric|min:0',
        ]);

        $hasSpecialTiers = $request->has('has_special_tiers')
            ? $request->boolean('has_special_tiers')
            : !empty($request->input('tiers'));

        DB::transaction(function () use ($validated, $hasSpecialTiers) {
            $package = TourPackage::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'duration' => $validated['duration'],
                'default_price' => $validated['default_price'],
                'status' => 'ACTIVE',
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($hasSpecialTiers && !empty($validated['tiers'])) {
                foreach ($validated['tiers'] as $tier) {
                    if (!empty($tier['price'])) {
                        TourPackagePrice::create([
                            'tour_package_id' => $package->id,
                            'min_guests' => $tier['min'],
                            'max_guests' => $tier['max'],
                            'price_per_guest' => $tier['price'],
                            'active' => true,
                        ]);
                    }
                }
            }

            AuditLog::log('CREATE_TOUR_PACKAGE', TourPackage::class, $package->id, null, ['name' => $package->name]);
        });

        return redirect()->route('packages.index')->with('success', 'Paket wisata berhasil dibuat.');
    }

    public function edit(TourPackage $package)
    {
        $package->load('prices');
        return view('packages.edit', compact('package'));
    }

    public function update(Request $request, TourPackage $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'duration' => 'required|string|max:50',
            'default_price' => 'required|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
            'notes' => 'nullable|string',
            'has_special_tiers' => 'nullable',
            'tiers' => 'nullable|array',
            'tiers.*.min' => 'required_with:tiers|integer|min:1',
            'tiers.*.max' => 'required_with:tiers|integer|min:1',
            'tiers.*.price' => 'required_with:tiers|numeric|min:0',
        ]);

        $hasSpecialTiers = $request->has('has_special_tiers')
            ? $request->boolean('has_special_tiers')
            : !empty($request->input('tiers'));

        DB::transaction(function () use ($package, $validated, $hasSpecialTiers) {
            $package->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'duration' => $validated['duration'],
                'default_price' => $validated['default_price'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Replace or remove tiers depending on special package toggle
            $package->prices()->delete();
            if ($hasSpecialTiers && !empty($validated['tiers'])) {
                foreach ($validated['tiers'] as $tier) {
                    if (!empty($tier['price'])) {
                        TourPackagePrice::create([
                            'tour_package_id' => $package->id,
                            'min_guests' => $tier['min'],
                            'max_guests' => $tier['max'],
                            'price_per_guest' => $tier['price'],
                            'active' => true,
                        ]);
                    }
                }
            }

            AuditLog::log('UPDATE_TOUR_PACKAGE', TourPackage::class, $package->id);
        });

        return redirect()->route('packages.index')->with('success', 'Paket wisata berhasil diperbarui.');
    }

    public function destroy(TourPackage $package)
    {
        $packageName = $package->name;

        DB::transaction(function () use ($package, $packageName) {
            $package->prices()->delete();
            $package->delete();
            AuditLog::log('DELETE_TOUR_PACKAGE', TourPackage::class, $package->id, ['name' => $packageName], null);
        });

        return redirect()->route('packages.index')->with('success', "Paket wisata '{$packageName}' dan seluruh skema harga berhasil dihapus.");
    }
}
