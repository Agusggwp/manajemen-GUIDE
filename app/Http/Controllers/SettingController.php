<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GuideQueueState;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'company_name' => Setting::get('company_name', Setting::get('app_name', 'ARTDEVATA')),
            'company_tagline' => Setting::get('company_tagline', Setting::get('app_tagline', 'Guide Management')),
            'app_short_name' => Setting::get('app_short_name', 'A'),
            'guide_portal_title' => Setting::get('guide_portal_title', 'Portal Pemandu Wisata'),
            'app_footer_text' => Setting::get('app_footer_text', '© ' . date('Y') . ' ARTDEVATA. Hak cipta dilindungi.'),
            'company_logo' => Setting::get('company_logo', null),
            'company_phone' => Setting::get('company_phone', '+62 812-3456-7890'),
            'company_address' => Setting::get('company_address', 'Jl. Sunset Road, Seminyak, Kuta, Bali'),
            'gps_required' => Setting::get('gps_required', '0'),
            'default_guide_fee' => Setting::get('default_guide_fee', '50000'),
            'expense_categories' => Setting::getExpenseCategories(),
        ];

        $queueState = GuideQueueState::getState();

        return view('settings.index', compact('settings', 'queueState'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:150',
            'company_tagline' => 'nullable|string|max:150',
            'app_short_name' => 'nullable|string|max:10',
            'guide_portal_title' => 'nullable|string|max:150',
            'app_footer_text' => 'nullable|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'remove_logo' => 'nullable|boolean',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'gps_required' => 'nullable|in:0,1',
            'default_guide_fee' => 'required|numeric|min:0',
            'current_position' => 'nullable|integer|min:0',
            'expense_categories' => 'nullable|array',
            'expense_categories.*' => 'nullable|string|max:100',
        ]);

        Setting::set('company_name', $validated['company_name']);
        Setting::set('app_name', $validated['company_name']);
        Setting::set('company_tagline', $validated['company_tagline'] ?? 'Guide Management');
        Setting::set('app_tagline', $validated['company_tagline'] ?? 'Guide Management');
        Setting::set('app_short_name', $validated['app_short_name'] ?? substr($validated['company_name'], 0, 1));
        Setting::set('guide_portal_title', $validated['guide_portal_title'] ?? 'Portal Pemandu Wisata');
        Setting::set('app_footer_text', $validated['app_footer_text'] ?? ('© ' . date('Y') . ' ' . $validated['company_name'] . '. Hak cipta dilindungi.'));
        Setting::set('company_phone', $validated['company_phone'] ?? '');
        Setting::set('company_address', $validated['company_address'] ?? '');
        Setting::set('gps_required', $request->input('gps_required', '0'));
        Setting::set('default_guide_fee', (string) $validated['default_guide_fee']);

        if ($request->has('expense_categories')) {
            $categories = array_values(array_filter(array_map('trim', (array) $request->input('expense_categories'))));
            Setting::set('expense_categories', json_encode(!empty($categories) ? $categories : [
                'Tiket Masuk & Parkir',
                'Bahan Bakar & Tol',
                'Sewa Mobil / Bus Pariwisata',
                'Konsumsi & Air Mineral',
                'Lain-lain',
            ]));
        }

        // Handle Logo Removal or Upload
        if ($request->boolean('remove_logo')) {
            $existingLogo = Setting::get('company_logo');
            if ($existingLogo && file_exists(public_path($existingLogo))) {
                @unlink(public_path($existingLogo));
            }
            Setting::set('company_logo', '');
        } elseif ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $uploadDir = public_path('uploads/logos');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Remove previous custom logo file if exists
            $existingLogo = Setting::get('company_logo');
            if ($existingLogo && file_exists(public_path($existingLogo))) {
                @unlink(public_path($existingLogo));
            }

            $filename = 'logo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            Setting::set('company_logo', 'uploads/logos/' . $filename);
        }

        if (isset($validated['current_position'])) {
            $queueState = GuideQueueState::getState();
            $oldPos = $queueState->current_position;
            $queueState->current_position = (int) $validated['current_position'];
            $queueState->save();

            AuditLog::log('SET_QUEUE_POSITION', GuideQueueState::class, 1, ['current_position' => $oldPos], ['current_position' => $queueState->current_position]);
        }

        AuditLog::log('UPDATE_SETTINGS', Setting::class);

        return back()->with('success', 'Pengaturan sistem & logo berhasil diperbarui.');
    }
}
