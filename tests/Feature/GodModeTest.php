<?php

namespace Tests\Feature;

use App\Models\Letter;
use App\Models\LetterType;
use App\Models\Role;
use App\Models\School;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GodModeTest extends TestCase
{
    use DatabaseMigrations;

    protected SuperAdmin $superAdmin;
    protected School $schoolA;
    protected School $schoolB;
    protected User $adminA;
    protected User $guruA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = SuperAdmin::create([
            'name'     => 'Supreme Commander',
            'username' => 'godmode',
            'password' => Hash::make('secretgod123'),
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $guruRole  = Role::firstOrCreate(['name' => 'guru']);

        $this->schoolA = School::create([
            'name'         => 'SMA Negeri 1 Nusa',
            'subdomain'    => 'sman1nusa',
            'package_tier' => 'menengah',
            'is_active'    => true,
        ]);

        $this->schoolB = School::create([
            'name'         => 'SMP Negeri 2 Merdeka',
            'subdomain'    => 'smpn2merdeka',
            'package_tier' => 'dasar',
            'is_active'    => false, // Sekolah nonaktif
        ]);

        $this->adminA = User::create([
            'school_id' => $this->schoolA->id,
            'role_id'   => $adminRole->id,
            'username'  => 'admin_sman1',
            'name'      => 'Admin SMAN 1',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->guruA = User::create([
            'school_id' => $this->schoolA->id,
            'role_id'   => $guruRole->id,
            'username'  => 'guru_budi',
            'name'      => 'Budi Hartono, S.Pd.',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_view_god_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->get(route('god.dashboard'));

        $response->assertOk();
        $response->assertSee('Kontrol Global Platform');
        $response->assertSee('SMA Negeri 1 Nusa');
        $response->assertSee('SMP Negeri 2 Merdeka');
    }

    public function test_super_admin_can_impersonate_any_school_including_inactive(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post(route('god.impersonate', $this->schoolB));

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(session('god_impersonating'));
        $this->assertEquals($this->schoolB->id, session('god_school_id'));
        $this->assertEquals($this->superAdmin->id, session('god_id'));

        // Terautentikasi di guard web
        $this->assertTrue(Auth::guard('web')->check());
        $this->assertEquals($this->schoolB->id, Auth::guard('web')->user()->school_id);
    }

    public function test_super_admin_can_impersonate_school_without_admin_auto_provisions(): void
    {
        $schoolEmpty = School::create([
            'name'         => 'SMK Baru Belum Ada Akun',
            'subdomain'    => 'smkbaru',
            'package_tier' => 'atas',
            'is_active'    => true,
        ]);

        $this->assertEquals(0, $schoolEmpty->users()->count());

        $response = $this->actingAs($this->superAdmin, 'super')
            ->post(route('god.impersonate', $schoolEmpty));

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(session('god_impersonating'));

        // Akun admin otomatis dibuatkan
        $this->assertEquals(1, $schoolEmpty->users()->count());
        $createdAdmin = $schoolEmpty->users()->first();
        $this->assertEquals('admin_smkbaru', $createdAdmin->username);
        $this->assertEquals('admin', $createdAdmin->role->name);
    }

    public function test_super_admin_can_impersonate_specific_user_in_school(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post(route('god.impersonate.user', $this->guruA));

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(session('god_impersonating'));
        $this->assertEquals($this->guruA->id, Auth::guard('web')->id());
        $this->assertEquals('guru_budi', Auth::guard('web')->user()->username);
    }

    public function test_god_mode_user_can_switch_school_instantly(): void
    {
        // Masuk ke school A via impersonate
        $this->actingAs($this->superAdmin, 'super')
            ->post(route('god.impersonate', $this->schoolA));

        $this->assertEquals($this->schoolA->id, session('god_school_id'));

        // Pindah sekolah instan ke school B
        $response = $this->post(route('god.switch-school'), [
            'school_id' => $this->schoolB->id,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($this->schoolB->id, session('god_school_id'));
        $this->assertEquals($this->schoolB->id, Auth::guard('web')->user()->school_id);
    }

    public function test_regular_user_cannot_switch_school_without_god_mode(): void
    {
        $response = $this->actingAs($this->adminA, 'web')
            ->post(route('god.switch-school'), [
                'school_id' => $this->schoolB->id,
            ]);

        $response->assertForbidden();
    }

    public function test_super_admin_can_update_school_details(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->put(route('god.schools.update', $this->schoolA), [
                'name'         => 'SMA Negeri 1 Nusa Reborn',
                'subdomain'    => 'sman1nusa-reborn',
                'package_tier' => 'atas',
                'is_active'    => 1,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('schools', [
            'id'           => $this->schoolA->id,
            'name'         => 'SMA Negeri 1 Nusa Reborn',
            'subdomain'    => 'sman1nusa-reborn',
            'package_tier' => 'atas',
        ]);
    }

    public function test_super_admin_can_exit_god_mode_and_restore_super_session(): void
    {
        // Masuk God Mode
        $this->actingAs($this->superAdmin, 'super')
            ->post(route('god.impersonate', $this->schoolA));

        $this->assertTrue(session('god_impersonating'));

        // Keluar God Mode
        $response = $this->get(route('god.exit'));

        $response->assertRedirect(route('god.dashboard'));
        $this->assertFalse(session()->has('god_impersonating'));
        $this->assertFalse(Auth::guard('web')->check());
        $this->assertTrue(Auth::guard('super')->check());
        $this->assertEquals($this->superAdmin->id, Auth::guard('super')->id());
    }

    public function test_god_master_bar_renders_when_god_mode_is_active(): void
    {
        $response = $this->actingAs($this->adminA, 'web')
            ->withSession([
                'god_impersonating'      => true,
                'god_school_id'          => $this->schoolA->id,
                'god_school_name'        => $this->schoolA->name,
                'god_school_subdomain'   => $this->schoolA->subdomain,
                'god_id'                 => $this->superAdmin->id,
            ])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('god-master-bar', false);
        $response->assertSee('⚡ GOD MODE');
        $response->assertSee($this->schoolA->name);
        $response->assertSee(route('god.switch-school'));
    }

    public function test_multi_tenant_letter_isolation_global_scope(): void
    {
        $letterTypeA = LetterType::create([
            'school_id'   => $this->schoolA->id,
            'name'        => 'Surat Tugas A',
            'code'        => 'ST-A',
            'category'    => 'surat_keluar',
            'template'    => 'Template A',
            'format_rule' => '{nomor}/ST/{bulan_romawi}/{tahun}',
        ]);

        $letterTypeB = LetterType::create([
            'school_id'   => $this->schoolB->id,
            'name'        => 'Surat Tugas B',
            'code'        => 'ST-B',
            'category'    => 'surat_keluar',
            'template'    => 'Template B',
            'format_rule' => '{nomor}/ST/{bulan_romawi}/{tahun}',
        ]);

        $letterA = Letter::create([
            'school_id'        => $this->schoolA->id,
            'letter_type_id'   => $letterTypeA->id,
            'category'         => 'surat_keluar',
            'reference_number' => '001/ST/IX/2026',
            'sequence_number'  => 1,
            'year'             => 2026,
            'subject'          => 'Surat Tugas Guru A',
            'content'          => '<p>Isi tugas guru A</p>',
            'letter_date'      => '2026-09-29',
            'status'           => 'disetujui',
            'created_by'       => $this->adminA->id,
        ]);

        $letterB = Letter::create([
            'school_id'        => $this->schoolB->id,
            'letter_type_id'   => $letterTypeB->id,
            'category'         => 'surat_keluar',
            'reference_number' => '001/ST/IX/2026',
            'sequence_number'  => 1,
            'year'             => 2026,
            'subject'          => 'Surat Tugas Guru B',
            'content'          => '<p>Isi tugas guru B</p>',
            'letter_date'      => '2026-09-29',
            'status'           => 'disetujui',
            'created_by'       => $this->adminA->id,
        ]);

        // Login sebagai Admin di School A
        $this->actingAs($this->adminA, 'web');

        // BelongsToSchool global scope mengisolasi query
        $letters = Letter::all();
        $this->assertCount(1, $letters);
        $this->assertEquals($letterA->id, $letters->first()->id);
        $this->assertEquals('Surat Tugas Guru A', $letters->first()->subject);

        $letterTypes = LetterType::all();
        $this->assertCount(1, $letterTypes);
        $this->assertEquals($letterTypeA->id, $letterTypes->first()->id);
    }

    public function test_super_admin_can_fetch_live_telemetry_json(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->get(route('god.telemetry'));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'timestamp',
            'data' => [
                'server' => ['cpu', 'memory', 'disk', 'traffic'],
                'runtime',
                'platform',
                'role_dist',
                'tier_dist',
                'recent_logs',
                'traffic_7d',
            ],
        ]);
    }

    public function test_super_admin_can_clear_server_cache(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post(route('god.server.clear-cache'));

        $response->assertRedirect();
        $response->assertSessionHas('toast');
    }

    public function test_super_admin_can_rebuild_server_cache(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post(route('god.server.rebuild-cache'));

        $response->assertRedirect();
        $response->assertSessionHas('toast');
    }

    public function test_super_admin_can_view_and_save_seo_and_adsense(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->get(route('god.seo'));

        $response->assertOk();
        $response->assertSee('Google Search Console & Analytics');
        $response->assertSee('Google AdSense');

        $postResponse = $this->actingAs($this->superAdmin, 'super')
            ->post(route('god.seo.save'), [
                'google_site_verification' => 'google-meta-code-xyz',
                'ga4_measurement_id'       => 'G-TEST12345',
                'seo_meta_title'           => 'SIM Sekolah Terbaik',
                'seo_meta_description'     => 'Deskripsi lengkap platform SIM sekolah modern.',
                'seo_meta_keywords'        => 'ruang gtk, sim, sekolah',
                'adsense_enabled'          => '1',
                'adsense_client_id'        => 'ca-pub-9988776655443322',
                'adsense_auto_ads'         => '1',
                'ads_txt'                  => "google.com, pub-9988776655443322, DIRECT, f08c47fec0942fa0\n",
            ]);

        $postResponse->assertRedirect();
        $postResponse->assertSessionHas('toast');

        $this->assertEquals('google-meta-code-xyz', \App\Models\SiteSetting::get('google_site_verification'));
        $this->assertEquals('ca-pub-9988776655443322', \App\Models\SiteSetting::get('adsense_client_id'));
    }

    public function test_public_ads_txt_and_sitemap_xml(): void
    {
        \App\Models\SiteSetting::set('ads_txt', "google.com, pub-12345, DIRECT, f08c47fec0942fa0\n");

        $adsResponse = $this->get('/ads.txt');
        $adsResponse->assertOk();
        $adsResponse->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $this->assertStringContainsString('pub-12345', $adsResponse->getContent());

        $sitemapResponse = $this->get('/sitemap.xml');
        $sitemapResponse->assertOk();
        $sitemapResponse->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->assertStringContainsString('<urlset', $sitemapResponse->getContent());
    }

    public function test_super_admin_can_view_server_maintenance_and_live_traffic(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->get(route('god.server'));

        $response->assertOk();
        $response->assertSee('Trafik Langsung Real-Time (Live Feed)');
        $response->assertSee('Pembersihan & Optimasi Database');

        $trafficResponse = $this->actingAs($this->superAdmin, 'super')
            ->get(route('god.server.live-traffic'));

        $trafficResponse->assertOk();
        $trafficResponse->assertJsonStructure([
            'success',
            'data',
        ]);
    }

    public function test_super_admin_can_download_database_backup(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->get(route('god.server.backup'));

        $response->assertOk();
        $this->assertTrue(
            $response->headers->contains('content-type', 'application/x-sqlite3') ||
            $response->headers->contains('content-type', 'application/sql')
        );
    }

    public function test_super_admin_can_clean_database(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post(route('god.server.clean-database'));

        $response->assertRedirect();
        $response->assertSessionHas('toast');
    }

    public function test_super_admin_can_update_branding_and_logo_settings(): void
    {
        $response = $this->actingAs($this->superAdmin, 'super')
            ->get(route('god.cms'));

        $response->assertOk();
        $response->assertSee('Identitas Brand & Konten Landing');

        $saveResponse = $this->actingAs($this->superAdmin, 'super')
            ->post(route('god.cms.save'), [
                'site_name'        => 'Ruang GTK Pro',
                'site_headline'    => 'Platform Manajemen Sekolah Pintar',
                'site_tagline'     => 'Solusi komprehensif seluruh sekolah Indonesia.',
                'site_logo'        => 'img/custom-logo.svg',
                'site_favicon'     => 'img/custom-favicon.ico',
                'site_hero_image'  => 'img/custom-hero.svg',
                'site_footer_text' => 'Ruang GTK 2026',
            ]);

        $saveResponse->assertRedirect();
        $saveResponse->assertSessionHas('toast');

        $this->assertEquals('Ruang GTK Pro', \App\Models\SiteSetting::get('site_name'));
        $this->assertEquals('Platform Manajemen Sekolah Pintar', \App\Models\SiteSetting::get('site_headline'));
    }
}
