<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GuideQueueState;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TourPackage;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request)
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
            'default_phone_fee' => Setting::get('default_phone_fee', '0'),
            'default_driver_fee' => Setting::get('default_driver_fee', '50000'),
            'expense_categories' => Setting::getExpenseCategories(),
            'operational_auto_schedule_enabled' => Setting::get('operational_auto_schedule_enabled', '0'),
            'operational_auto_open_time' => Setting::get('operational_auto_open_time', '07:00'),
            'operational_auto_close_time' => Setting::get('operational_auto_close_time', '18:00'),
            'discord_webhook_enabled' => Setting::get('discord_webhook_enabled', '0'),
            'discord_webhook_url' => Setting::get('discord_webhook_url', ''),
            'discord_activity_webhook_url' => Setting::get('discord_activity_webhook_url', ''),
        ];

        $operationalShift = app(\App\Services\OperationalShiftService::class)->getStatus();
        $queueState = GuideQueueState::getState();
        $tourPackages = TourPackage::with('prices')->orderBy('name')->get();
        $activeTab = $request->query('tab', 'brand');
        $discordService = app(\App\Services\DiscordWebhookService::class);
        $discordWebhookConfigured = !empty($discordService->getWebhookUrl());
        $discordActivityWebhookConfigured = !empty($discordService->getActivityWebhookUrl());

        $roles = Role::withCount('users')->orderBy('id')->get();
        $availablePermissions = Role::availablePermissions();

        return view('settings.index', compact(
            'settings',
            'operationalShift',
            'queueState',
            'activeTab',
            'tourPackages',
            'discordWebhookConfigured',
            'discordActivityWebhookConfigured',
            'roles',
            'availablePermissions'
        ));
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
            'default_phone_fee' => 'nullable|numeric|min:0',
            'default_driver_fee' => 'nullable|numeric|min:0',
            'current_position' => 'nullable|integer|min:0',
            'operational_auto_schedule_enabled' => 'nullable|in:0,1',
            'operational_auto_open_time' => 'nullable|string|max:10',
            'operational_auto_close_time' => 'nullable|string|max:10',
            'expense_categories' => 'nullable|array',
            'expense_categories.*' => 'nullable|string|max:100',
            'discord_webhook_url' => 'nullable|url|max:255',
            'discord_activity_webhook_url' => 'nullable|url|max:255',
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
        Setting::set('default_phone_fee', (string) ($validated['default_phone_fee'] ?? 0));
        Setting::set('default_driver_fee', (string) ($validated['default_driver_fee'] ?? 50000));
        Setting::set('operational_auto_schedule_enabled', $request->input('operational_auto_schedule_enabled', '0'));
        if ($request->filled('operational_auto_open_time')) {
            Setting::set('operational_auto_open_time', $request->input('operational_auto_open_time'));
        }
        if ($request->filled('operational_auto_close_time')) {
            Setting::set('operational_auto_close_time', $request->input('operational_auto_close_time'));
        }
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

        if ($request->has('discord_webhook_url')) {
            Setting::set('discord_webhook_url', $request->input('discord_webhook_url') ?: '');
        }
        if ($request->has('discord_activity_webhook_url')) {
            Setting::set('discord_activity_webhook_url', $request->input('discord_activity_webhook_url') ?: '');
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

        AuditLog::log('UPDATE_SETTINGS', Setting::class, null, null, [
            'company_name' => $validated['company_name'],
            'default_guide_fee' => $validated['default_guide_fee'],
        ]);

        $activeTab = $request->input('active_tab', 'brand');

        return redirect()->route('settings.index', ['tab' => $activeTab])
            ->with('success', 'Pengaturan sistem & logo berhasil diperbarui.');
    }

    /**
     * Send test notification to Discord Webhook using .env or database configuration.
     */
    public function testDiscordWebhook(Request $request, \App\Services\DiscordWebhookService $discordService)
    {
        $type = $request->input('type', 'operational');

        if ($type === 'activity') {
            $url = $request->input('discord_activity_webhook_url') ?: $discordService->getActivityWebhookUrl();
            $envVar = 'DISCORD_ACTIVITY_WEBHOOK_URL';
            $channelLabel = 'Aktivitas & Audit Trail';
        } else {
            $url = $request->input('discord_webhook_url') ?: $discordService->getWebhookUrl();
            $envVar = 'DISCORD_WEBHOOK_URL';
            $channelLabel = 'Operasional Loket';
        }

        if (empty($url)) {
            return response()->json([
                'success' => false,
                'message' => "URL Webhook Discord {$channelLabel} belum diatur di file .env ({$envVar}) atau form pengaturan. Silakan isi terlebih dahulu.",
            ], 422);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json([
                'success' => false,
                'message' => "Format URL Webhook Discord {$channelLabel} tidak valid.",
            ], 422);
        }

        $result = $discordService->sendTestNotification($url, $type);
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }
}
