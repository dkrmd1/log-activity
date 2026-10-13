<?php

namespace Tests\Feature;

use App\Models\MonthlyReport;
use App\Models\ServerCheck;
use App\Models\User;
use App\Support\Signature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Notifikasi tanda tangan / persetujuan, sesuai peran:
 *   Staf  -> mengisi form & mengajukan laporan   (memberi notifikasi ke Group Head)
 *   Group Head -> tanda tangan / setuju / tolak  (memberi notifikasi ke Staf)
 *   Admin -> tidak ada di rantai persetujuan     (tidak menerima notifikasi)
 */
class SignatureNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $staff2;
    private User $gh;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake(Signature::DISK);

        $this->staff = $this->makeUser('Staf Satu', 'staff');
        $this->staff2 = $this->makeUser('Staf Dua', 'staff');
        $this->gh = $this->makeUser('Group Head Satu', 'group_head');
        $this->admin = User::factory()->create(['name' => 'Admin', 'role' => 'admin']);
    }

    private function makeUser(string $name, string $role): User
    {
        $img = imagecreatetruecolor(40, 20);
        ob_start();
        imagepng($img);
        $png = ob_get_clean();

        return User::factory()->create([
            'name' => $name,
            'role' => $role,
            'signature' => Signature::put($png, 'png'),
        ]);
    }

    private function newCheck(): ServerCheck
    {
        return ServerCheck::createForDate('2026-09-01', $this->staff->id);
    }

    /** Payload yang mengisi SEMUA item di ketiga waktu pemeriksaan. */
    private function fullPayload(ServerCheck $check, string $value = 'OK'): array
    {
        $rows = [];

        foreach ($check->entries as $e) {
            $rows[$e->id] = ['c1' => $value, 'c2' => $value, 'c3' => $value];
        }

        return ['entries' => $rows];
    }

    private function titles(User $u): array
    {
        return $u->fresh()->notifications->pluck('data.title')->all();
    }

    // ---------- Laporan Bulanan ----------

    public function test_staf_mengajukan_laporan_memberi_notifikasi_ke_group_head_saja(): void
    {
        $this->newCheck();

        $this->actingAs($this->staff)
            ->post(route('reports.submit'), ['bulan' => 9, 'tahun' => 2026])
            ->assertRedirect();

        $this->assertSame(['Laporan Bulanan menunggu persetujuan'], $this->titles($this->gh));
        $this->assertCount(0, $this->staff->fresh()->notifications);
        $this->assertCount(0, $this->admin->fresh()->notifications);
        $this->assertSame('/reports?bulan=9&tahun=2026', $this->gh->fresh()->notifications->first()->data['url']);
    }

    public function test_group_head_menyetujui_memberi_notifikasi_ke_staf_pengaju(): void
    {
        $this->newCheck();
        $this->actingAs($this->staff)->post(route('reports.submit'), ['bulan' => 9, 'tahun' => 2026]);

        $this->actingAs($this->gh)
            ->post(route('reports.approve'), ['bulan' => 9, 'tahun' => 2026])
            ->assertRedirect();

        $this->assertSame(MonthlyReport::APPROVED, MonthlyReport::first()->status);
        $this->assertSame(['Laporan Bulanan disetujui'], $this->titles($this->staff));
        $this->assertCount(0, $this->staff2->fresh()->notifications);
        $this->assertCount(0, $this->admin->fresh()->notifications);
    }

    public function test_group_head_menolak_memberi_notifikasi_dengan_catatan(): void
    {
        $this->newCheck();
        $this->actingAs($this->staff)->post(route('reports.submit'), ['bulan' => 9, 'tahun' => 2026]);

        $this->actingAs($this->gh)
            ->post(route('reports.reject'), ['bulan' => 9, 'tahun' => 2026, 'review_note' => 'Data backup belum lengkap'])
            ->assertRedirect();

        $n = $this->staff->fresh()->notifications->first();
        $this->assertSame('Laporan Bulanan ditolak', $n->data['title']);
        $this->assertStringContainsString('Data backup belum lengkap', $n->data['message']);
    }

    // ---------- Form Kontrol Server ----------

    public function test_form_lengkap_memberi_notifikasi_ke_group_head_hanya_sekali(): void
    {
        $check = $this->newCheck();

        // Pengisian sebagian: belum lengkap -> belum ada notifikasi.
        $first = $check->entries->first();
        $this->actingAs($this->staff)
            ->put(route('server-checks.update', $check), ['entries' => [$first->id => ['c1' => 'OK']]])
            ->assertRedirect();
        $this->assertCount(0, $this->gh->fresh()->notifications);

        // Dilengkapi -> notifikasi "siap diperiksa".
        $check->load('entries');
        $this->actingAs($this->staff)
            ->put(route('server-checks.update', $check), $this->fullPayload($check))
            ->assertRedirect();
        $this->assertSame(['Form Kontrol Server siap diperiksa'], $this->titles($this->gh));

        // Diubah lagi sebelum ditandatangani -> TIDAK ada notifikasi baru (sudah lengkap sebelumnya).
        $check = $check->fresh()->load('entries');
        $this->actingAs($this->staff)
            ->put(route('server-checks.update', $check), $this->fullPayload($check, 'NOK'))
            ->assertRedirect();
        $this->assertCount(1, $this->gh->fresh()->notifications);
        $this->assertCount(0, $this->admin->fresh()->notifications);
    }

    public function test_group_head_menandatangani_memberi_notifikasi_ke_staf_bukan_admin(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));

        $this->actingAs($this->gh)
            ->post(route('server-checks.sign', $check))
            ->assertRedirect();

        $this->assertTrue($check->fresh()->isSigned());
        $this->assertContains('Form Kontrol Server ditandatangani', $this->titles($this->staff));
        $this->assertCount(0, $this->staff2->fresh()->notifications); // bukan pembuat / bukan paraf
        $this->assertCount(0, $this->admin->fresh()->notifications);
        $this->assertNotContains('Form Kontrol Server ditandatangani', $this->titles($this->gh)); // pelaku sendiri
    }

    public function test_batal_tanda_tangan_memberi_notifikasi_ke_staf(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $this->actingAs($this->gh)->post(route('server-checks.sign', $check));

        $this->actingAs($this->gh)->delete(route('server-checks.unsign', $check))->assertRedirect();

        $this->assertContains('Tanda tangan form dibatalkan', $this->titles($this->staff));
    }

    public function test_staf_mengubah_form_yang_sudah_ditandatangani_memberi_notifikasi_ttd_ulang(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $this->actingAs($this->gh)->post(route('server-checks.sign', $check));
        $this->assertTrue($check->fresh()->isSigned());

        $check = $check->fresh()->load('entries');
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check, 'NOK'));

        $this->assertFalse($check->fresh()->isSigned());
        $this->assertContains('Form perlu ditandatangani ulang', $this->titles($this->gh));
    }

    // ---------- Lonceng & halaman notifikasi ----------

    public function test_membuka_notifikasi_menandai_dibaca_dan_mengarahkan_ke_halaman_terkait(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));

        $n = $this->gh->fresh()->notifications->first();
        $this->assertNull($n->read_at);

        $this->actingAs($this->gh)
            ->get(route('notifications.read', $n->id))
            ->assertRedirect(route('server-checks.edit', $check, false));

        $this->assertNotNull($n->fresh()->read_at);
    }

    public function test_user_tidak_bisa_membuka_notifikasi_milik_orang_lain(): void
    {
        $this->newCheck();
        $this->actingAs($this->staff)->post(route('reports.submit'), ['bulan' => 9, 'tahun' => 2026]);

        $n = $this->gh->fresh()->notifications->first();

        $this->actingAs($this->staff)->get(route('notifications.read', $n->id))->assertNotFound();
        $this->assertNull($n->fresh()->read_at);
    }

    public function test_lonceng_dan_halaman_notifikasi_tampil(): void
    {
        $this->newCheck();
        $this->actingAs($this->staff)->post(route('reports.submit'), ['bulan' => 9, 'tahun' => 2026]);

        $this->actingAs($this->gh)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Laporan Bulanan menunggu persetujuan')
            ->assertSee('1 baru');

        $this->actingAs($this->gh)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Semua Notifikasi')
            ->assertSee('Laporan Bulanan menunggu persetujuan');

        $this->actingAs($this->gh)->post(route('notifications.read-all'))->assertRedirect();
        $this->assertCount(0, $this->gh->fresh()->unreadNotifications);
    }

    // ---------- Tolak form Kontrol Server (wajib keterangan) ----------

    public function test_tolak_form_wajib_ada_keterangan(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));

        $this->actingAs($this->gh)
            ->post(route('server-checks.reject', $check), ['reject_note' => ''])
            ->assertSessionHasErrors('reject_note');

        $this->actingAs($this->gh)
            ->post(route('server-checks.reject', $check), ['reject_note' => '   '])
            ->assertSessionHasErrors('reject_note');

        $this->assertFalse($check->fresh()->isRejected());
        $this->assertNotContains('Form Kontrol Server ditolak', $this->titles($this->staff));
    }

    public function test_group_head_menolak_form_staf_diberi_tahu_beserta_keterangan(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $before = $check->fresh()->updated_at;

        $this->actingAs($this->gh)
            ->post(route('server-checks.reject', $check), ['reject_note' => 'Kondisi AC jam 12:00 belum diisi'])
            ->assertRedirect();

        $fresh = $check->fresh();
        $this->assertTrue($fresh->isRejected());
        $this->assertFalse($fresh->isSigned());
        $this->assertSame('Group Head Satu', $fresh->rejected_name);
        $this->assertEquals($before, $fresh->updated_at); // penolakan bukan perubahan data (laporan bulanan tidak ikut "berubah")

        $n = $this->staff->fresh()->notifications->firstWhere('data.title', 'Form Kontrol Server ditolak');
        $this->assertNotNull($n);
        $this->assertStringContainsString('Kondisi AC jam 12:00 belum diisi', $n->data['message']);
        $this->assertCount(0, $this->staff2->fresh()->notifications);
        $this->assertCount(0, $this->admin->fresh()->notifications);
    }

    public function test_hanya_group_head_yang_bisa_menolak(): void
    {
        $check = $this->newCheck();

        foreach ([$this->staff, $this->admin] as $u) {
            $this->actingAs($u)
                ->post(route('server-checks.reject', $check), ['reject_note' => 'x'])
                ->assertForbidden();
        }

        $this->assertFalse($check->fresh()->isRejected());
    }

    public function test_staf_merevisi_form_ditolak_mencabut_penolakan_dan_memberi_tahu_group_head(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $this->actingAs($this->gh)->post(route('server-checks.reject', $check), ['reject_note' => 'Perbaiki']);
        $this->assertTrue($check->fresh()->isRejected());

        // Simpan TANPA perubahan: penolakan tetap ada.
        $check = $check->fresh()->load('entries');
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $this->assertTrue($check->fresh()->isRejected());

        // Simpan dengan perubahan nyata: penolakan dicabut + Group Head diberi tahu.
        $check = $check->fresh()->load('entries');
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check, 'NOK'));

        $this->assertFalse($check->fresh()->isRejected());
        $this->assertContains('Form Kontrol Server sudah direvisi', $this->titles($this->gh));
    }

    public function test_menandatangani_form_yang_ditolak_mencabut_penolakan(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $this->actingAs($this->gh)->post(route('server-checks.reject', $check), ['reject_note' => 'Cek lagi']);

        $this->actingAs($this->gh)->post(route('server-checks.sign', $check))->assertRedirect();

        $this->assertTrue($check->fresh()->isSigned());
        $this->assertFalse($check->fresh()->isRejected());
    }

    // ---------- Tanda tangan OTOMATIS saat Group Head membuka form ----------

    public function test_group_head_membuka_form_lengkap_otomatis_ditandatangani_tanpa_tombol_approve(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $this->assertFalse($check->fresh()->isSigned());

        $this->actingAs($this->gh)->get(route('server-checks.edit', $check))
            ->assertOk()
            ->assertSee('ditandatangani otomatis atas nama Anda')
            ->assertSee('Group Head Satu')
            ->assertSee('Reject')
            ->assertDontSee('Approve')
            ->assertDontSee('Batalkan');

        $fresh = $check->fresh();
        $this->assertTrue($fresh->isSigned());
        $this->assertSame($this->gh->id, (int) $fresh->signed_by);
        $this->assertContains('Form Kontrol Server ditandatangani', $this->titles($this->staff));

        // Dibuka lagi: tidak menandatangani ulang dan tidak mengirim notifikasi dobel.
        $this->actingAs($this->gh)->get(route('server-checks.edit', $check))->assertOk();
        $this->assertCount(1, array_keys(array_filter($this->titles($this->staff), fn ($t) => $t === 'Form Kontrol Server ditandatangani')));
    }

    public function test_form_belum_lengkap_tidak_ditandatangani_saat_dibuka(): void
    {
        $check = $this->newCheck();
        $first = $check->entries->first();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), ['entries' => [$first->id => ['c1' => 'OK']]]);

        $this->actingAs($this->gh)->get(route('server-checks.edit', $check))
            ->assertOk()
            ->assertSee('belum lengkap')
            ->assertDontSee('ditandatangani otomatis atas nama Anda');

        $this->assertFalse($check->fresh()->isSigned());
    }

    public function test_hanya_group_head_yang_memicu_tanda_tangan_otomatis(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));

        foreach ([$this->staff, $this->staff2, $this->admin] as $u) {
            $this->actingAs($u)->get(route('server-checks.edit', $check))->assertOk();
        }

        $this->assertFalse($check->fresh()->isSigned());
    }

    public function test_group_head_tanpa_tanda_tangan_profil_tidak_menandatangani_dan_diberi_peringatan(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $this->gh->update(['signature' => null]);

        $this->actingAs($this->gh->fresh())->get(route('server-checks.edit', $check))
            ->assertOk()
            ->assertSee('belum ada di Profil');

        $this->assertFalse($check->fresh()->isSigned());
    }

    public function test_form_yang_ditolak_tidak_ditandatangani_otomatis_sampai_direvisi(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $this->actingAs($this->gh)->get(route('server-checks.edit', $check)); // otomatis ditandatangani
        $this->actingAs($this->gh)->post(route('server-checks.reject', $check), ['reject_note' => 'Salah isi']);

        // Ditolak: ttd dicabut dan tidak terpasang lagi saat dibuka.
        $this->assertFalse($check->fresh()->isSigned());
        $this->actingAs($this->gh)->get(route('server-checks.edit', $check))->assertOk()->assertSee('menunggu Staf merevisi');
        $this->assertFalse($check->fresh()->isSigned());

        // Staf merevisi -> Group Head membuka lagi -> otomatis ditandatangani.
        $check = $check->fresh()->load('entries');
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check, 'NOK'));
        $this->actingAs($this->gh)->get(route('server-checks.edit', $check))->assertOk();

        $fresh = $check->fresh();
        $this->assertTrue($fresh->isSigned());
        $this->assertFalse($fresh->isRejected());
    }

    public function test_reject_form_yang_sudah_ditandatangani_mencabut_tanda_tangan(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $this->actingAs($this->gh)->get(route('server-checks.edit', $check));
        $this->assertTrue($check->fresh()->isSigned());

        $this->actingAs($this->gh)
            ->post(route('server-checks.reject', $check), ['reject_note' => 'Ulangi pemeriksaan jam 16:30'])
            ->assertRedirect();

        $fresh = $check->fresh();
        $this->assertFalse($fresh->isSigned());
        $this->assertNull($fresh->signed_signature);
        $this->assertTrue($fresh->isRejected());
        $this->assertContains('Form Kontrol Server ditolak', $this->titles($this->staff));
    }

    public function test_staf_mengubah_form_yang_otomatis_ditandatangani_memberi_notifikasi_ttd_ulang(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $this->actingAs($this->gh)->get(route('server-checks.edit', $check));
        $this->assertTrue($check->fresh()->isSigned());

        $check = $check->fresh()->load('entries');
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check, 'NOK'));

        $this->assertFalse($check->fresh()->isSigned());
        $this->assertContains('Form perlu ditandatangani ulang', $this->titles($this->gh));

        // Group Head membuka lagi -> terpasang otomatis lagi.
        $this->actingAs($this->gh)->get(route('server-checks.edit', $check))->assertOk();
        $this->assertTrue($check->fresh()->isSigned());
    }

    // ---------- Daftar Kontrol Server: Reject saja untuk Group Head ----------

    public function test_daftar_form_group_head_hanya_melihat_reject_bukan_approve_atau_hapus(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));

        $this->actingAs($this->gh)->get(route('server-checks.index'))
            ->assertOk()
            ->assertSee('title="Reject"', false)
            ->assertSee('id="rejectModal"', false)
            ->assertDontSee('title="Approve"', false)
            ->assertDontSee('title="Hapus"', false);

        // Staf & Admin tetap melihat Hapus, tanpa Reject.
        foreach ([$this->staff, $this->admin] as $u) {
            $this->actingAs($u)->get(route('server-checks.index'))
                ->assertOk()
                ->assertSee('title="Hapus"', false)
                ->assertDontSee('title="Reject"', false)
                ->assertDontSee('id="rejectModal"', false);
        }
    }

    public function test_reject_dari_daftar_wajib_keterangan_dan_berhasil_dengan_keterangan(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));

        $this->actingAs($this->gh)->from(route('server-checks.index'))
            ->post(route('server-checks.reject', $check), ['reject_note' => ''])
            ->assertRedirect(route('server-checks.index'))
            ->assertSessionHasErrors('reject_note');

        $this->actingAs($this->gh)->from(route('server-checks.index'))
            ->post(route('server-checks.reject', $check), ['reject_note' => 'Ulangi pemeriksaan jam 16:30'])
            ->assertRedirect(route('server-checks.index'));

        $this->assertTrue($check->fresh()->isRejected());
    }

    public function test_keterangan_penolakan_tampil_untuk_staf_di_halaman_form_dan_daftar(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fullPayload($check));
        $this->actingAs($this->gh)->post(route('server-checks.reject', $check), ['reject_note' => 'Paraf baris 3 kurang']);

        $this->actingAs($this->staff)->get(route('server-checks.edit', $check))
            ->assertOk()
            ->assertSee('Ditolak oleh Group Head Satu')
            ->assertSee('Paraf baris 3 kurang')
            ->assertSee('Perbaiki isi form');

        $this->actingAs($this->staff)->get(route('server-checks.index'))
            ->assertOk()
            ->assertSee('Ditolak');
    }

    // ---------- Laporan Bulanan: persetujuan OTOMATIS saat Group Head membuka ----------

    private function ajukanLaporan(): void
    {
        $this->newCheck();
        $this->actingAs($this->staff)->post(route('reports.submit'), ['bulan' => 9, 'tahun' => 2026])->assertRedirect();
        $this->assertSame(MonthlyReport::SUBMITTED, MonthlyReport::first()->status);
    }

    public function test_laporan_otomatis_disetujui_saat_group_head_membuka_tanpa_tombol_approve(): void
    {
        $this->ajukanLaporan();

        $this->actingAs($this->gh)->get(route('reports.index', ['bulan' => 9, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('ditandatangani otomatis atas nama Anda')
            ->assertSee('Reject')
            ->assertDontSee('Approve')
            ->assertDontSee('Setujui (ACC)');

        $report = MonthlyReport::first();
        $this->assertSame(MonthlyReport::APPROVED, $report->status);
        $this->assertSame($this->gh->id, (int) $report->reviewed_by);
        $this->assertNotEmpty($report->reviewed_signature);
        $this->assertContains('Laporan Bulanan disetujui', $this->titles($this->staff));
    }

    public function test_staf_dan_admin_membuka_laporan_tidak_menyetujui(): void
    {
        $this->ajukanLaporan();

        foreach ([$this->staff, $this->staff2, $this->admin] as $u) {
            $this->actingAs($u)->get(route('reports.index', ['bulan' => 9, 'tahun' => 2026]))->assertOk();
        }

        $this->assertSame(MonthlyReport::SUBMITTED, MonthlyReport::first()->status);
    }

    public function test_laporan_tidak_disetujui_otomatis_kalau_data_berubah_setelah_diajukan(): void
    {
        $this->ajukanLaporan();
        ServerCheck::createForDate('2026-09-02', $this->staff->id); // data bulan ini bertambah

        $this->actingAs($this->gh)->get(route('reports.index', ['bulan' => 9, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('berubah');

        $this->assertSame(MonthlyReport::SUBMITTED, MonthlyReport::first()->status);
    }

    public function test_laporan_tidak_disetujui_otomatis_kalau_group_head_belum_punya_tanda_tangan(): void
    {
        $this->ajukanLaporan();
        $this->gh->update(['signature' => null]);

        $this->actingAs($this->gh->fresh())->get(route('reports.index', ['bulan' => 9, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('ditandatangani otomatis saat Anda membuka halaman ini');

        $this->assertSame(MonthlyReport::SUBMITTED, MonthlyReport::first()->status);
    }

    public function test_reject_laporan_yang_sudah_otomatis_disetujui_mencabut_persetujuan_dan_tanda_tangan(): void
    {
        $this->ajukanLaporan();
        $this->actingAs($this->gh)->get(route('reports.index', ['bulan' => 9, 'tahun' => 2026]));
        $this->assertSame(MonthlyReport::APPROVED, MonthlyReport::first()->status);

        $this->actingAs($this->gh)
            ->post(route('reports.reject'), ['bulan' => 9, 'tahun' => 2026, 'review_note' => 'Angka backup salah'])
            ->assertRedirect();

        $report = MonthlyReport::first();
        $this->assertSame(MonthlyReport::REJECTED, $report->status);
        $this->assertNull($report->reviewed_signature);
        $this->assertSame('Angka backup salah', $report->review_note);

        $n = $this->staff->fresh()->notifications->firstWhere('data.title', 'Laporan Bulanan ditolak');
        $this->assertNotNull($n);

        // Dibuka lagi: laporan yang ditolak tidak disetujui otomatis (menunggu Staf mengajukan ulang).
        $this->actingAs($this->gh)->get(route('reports.index', ['bulan' => 9, 'tahun' => 2026]))->assertOk();
        $this->assertSame(MonthlyReport::REJECTED, MonthlyReport::first()->status);
    }
}
