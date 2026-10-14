<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SettingLogoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@artdevata.com',
            'role' => 'ADMIN',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'email' => 'staff@artdevata.com',
            'role' => 'STAFF',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_settings_page_with_logo_section(): void
    {
        $response = $this->actingAs($this->admin)->get(route('settings.index'));
        $response->assertStatus(200);
        $response->assertSee('Identitas Brand');
        $response->assertSee('Unggah File Logo Baru');
    }

    public function test_admin_can_upload_custom_logo_and_update_tagline(): void
    {
        $file = UploadedFile::fake()->image('custom_logo.png', 200, 200);

        $response = $this->actingAs($this->admin)->post(route('settings.update'), [
            'company_name' => 'ARTDEVATA BALI TOURS',
            'company_tagline' => 'Premium Bali Tour Services',
            'app_short_name' => 'ABT',
            'guide_portal_title' => 'Portal Guide Bali',
            'app_footer_text' => '© 2026 Artdevata Tour. Hak Cipta.',
            'company_logo' => $file,
            'default_guide_fee' => 60000,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('ARTDEVATA BALI TOURS', Setting::get('company_name'));
        $this->assertEquals('Premium Bali Tour Services', Setting::get('company_tagline'));
        $this->assertEquals('ABT', Setting::get('app_short_name'));
        $this->assertEquals('Portal Guide Bali', Setting::get('guide_portal_title'));
        $this->assertEquals('© 2026 Artdevata Tour. Hak Cipta.', Setting::get('app_footer_text'));

        $logoPath = Setting::get('company_logo');
        $this->assertNotEmpty($logoPath);
        $this->assertFileExists(public_path($logoPath));

        // Clean up created fake file
        if (file_exists(public_path($logoPath))) {
            @unlink(public_path($logoPath));
        }
    }

    public function test_admin_can_remove_custom_logo(): void
    {
        // First set a dummy logo
        $uploadDir = public_path('uploads/logos');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $dummyPath = 'uploads/logos/dummy_test_logo.png';
        file_put_contents(public_path($dummyPath), 'test');
        Setting::set('company_logo', $dummyPath);

        $this->assertFileExists(public_path($dummyPath));

        // Submit removal
        $response = $this->actingAs($this->admin)->post(route('settings.update'), [
            'company_name' => 'ARTDEVATA',
            'company_tagline' => 'Guide Management',
            'remove_logo' => 1,
            'default_guide_fee' => 50000,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('', Setting::get('company_logo'));
        $this->assertFileDoesNotExist(public_path($dummyPath));
    }

    public function test_staff_cannot_update_settings(): void
    {
        $response = $this->actingAs($this->staff)->post(route('settings.update'), [
            'company_name' => 'Hacked',
            'default_guide_fee' => 10000,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_expense_categories(): void
    {
        // 1. Initial default categories
        $defaults = Setting::getExpenseCategories();
        $this->assertContains('Tiket Masuk & Parkir', $defaults);
        $this->assertContains('Bahan Bakar & Tol', $defaults);

        // 2. Admin updates categories
        $customCategories = [
            'Tiket Wisata & Retribusi Pemda',
            'Bensin & Tol',
            'Sewa Alphard / HiAce',
            'Snack & Makan Siang',
            'Tips Kru & Helper',
        ];

        $response = $this->actingAs($this->admin)->post(route('settings.update'), [
            'company_name' => 'ARTDEVATA',
            'default_guide_fee' => 50000,
            'expense_categories' => $customCategories,
        ]);

        $response->assertSessionHas('success');

        // 3. Verify updated categories
        $updated = Setting::getExpenseCategories();
        $this->assertEquals($customCategories, $updated);
        $this->assertContains('Tips Kru & Helper', $updated);

        // 4. Verify rendered in expenses index modal
        $expensesPage = $this->actingAs($this->admin)->get(route('expenses.index'));
        $expensesPage->assertStatus(200);
        $expensesPage->assertSee('Tips Kru & Helper');
        $expensesPage->assertSee('Sewa Alphard / HiAce');
    }

    public function test_admin_can_update_default_phone_fee(): void
    {
        $response = $this->actingAs($this->admin)->post(route('settings.update'), [
            'company_name' => 'ARTDEVATA',
            'default_guide_fee' => 50000,
            'default_phone_fee' => 25000,
            'default_driver_fee' => 60000,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('25000', Setting::get('default_phone_fee'));
        $this->assertEquals('60000', Setting::get('default_driver_fee'));
    }

    public function test_admin_can_access_settings_sub_menu_tabs(): void
    {
        // 1. Check Brand Tab
        $resBrand = $this->actingAs($this->admin)->get(route('settings.index', ['tab' => 'brand']));
        $resBrand->assertStatus(200);
        $resBrand->assertSee('Identitas & Brand', false);
        $resBrand->assertSee('Unggah File Logo Baru');

        // 2. Check Tariffs Tab
        $resTariffs = $this->actingAs($this->admin)->get(route('settings.index', ['tab' => 'tariffs']));
        $resTariffs->assertStatus(200);
        $resTariffs->assertSee('Tarif & Biaya Tour', false);
        $resTariffs->assertSee('Tarif Standar Fee Guide per Tamu');
        $resTariffs->assertSee('Tarif Standar Biaya Telepon / Tour');
        $resTariffs->assertSee('Tarif Standar Uang Driver');

        // 3. Check Categories Tab
        $resCategories = $this->actingAs($this->admin)->get(route('settings.index', ['tab' => 'categories']));
        $resCategories->assertStatus(200);
        $resCategories->assertSee('Pilihan Kategori Biaya Operasional');

        // 4. Check Packages Tab
        $resPackages = $this->actingAs($this->admin)->get(route('settings.index', ['tab' => 'packages']));
        $resPackages->assertStatus(200);
        $resPackages->assertSee('Paket Wisata');
        $resPackages->assertSee('Katalog Paket Wisata');
        $resPackages->assertSee('+ Tambah Paket Baru');

        // 5. Check Operational Tab
        $resOperational = $this->actingAs($this->admin)->get(route('settings.index', ['tab' => 'operational']));
        $resOperational->assertStatus(200);
        $resOperational->assertSee('Validasi & Antrean', false);
        $resOperational->assertSee('Validasi GPS Lokasi');
        $resOperational->assertSee('Kontrol Rotasi Antrean Guide');

        // 6. Test saving with active_tab preserves tab parameter
        $resUpdate = $this->actingAs($this->admin)->post(route('settings.update'), [
            'company_name' => 'ARTDEVATA',
            'default_guide_fee' => 50000,
            'default_phone_fee' => 15000,
            'default_driver_fee' => 75000,
            'active_tab' => 'tariffs',
        ]);
        $resUpdate->assertRedirect(route('settings.index', ['tab' => 'tariffs']));
        $this->assertEquals('75000', Setting::get('default_driver_fee'));
    }

    public function test_admin_can_update_tour_package_from_settings(): void
    {
        $pkg = \App\Models\TourPackage::create([
            'name' => 'Ubud Cultural Tour',
            'duration' => '8 Jam',
            'default_price' => 150000,
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->admin)->put(route('packages.update', $pkg), [
            'name' => 'Ubud Cultural & Waterfall Tour',
            'duration' => '9 Jam',
            'default_price' => 175000,
            'status' => 'ACTIVE',
            'from' => 'settings',
        ]);

        $response->assertRedirect(route('settings.index', ['tab' => 'packages']));
        $this->assertEquals('Ubud Cultural & Waterfall Tour', $pkg->fresh()->name);
        $this->assertEquals(175000, (float) $pkg->fresh()->default_price);
        $this->assertEquals('9 Jam', $pkg->fresh()->duration);
    }
}
