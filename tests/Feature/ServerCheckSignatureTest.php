<?php

namespace Tests\Feature;

use App\Models\ServerCheck;
use App\Models\User;
use App\Support\Signature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Aturan Kontrol Server & Jaringan:
 *   - Tanda tangan form (blok kanan bawah) HANYA oleh Group Head.
 *   - Paraf per baris = tanda tangan staf yang mengisi. Hanya Staf yang membuat/mengisi form.
 */
class ServerCheckSignatureTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $gh;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake(Signature::DISK);

        $this->staff = $this->makeUser('Staf Satu', 'staff');
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

    private function newCheck(?User $by = null): ServerCheck
    {
        return ServerCheck::createForDate('2026-09-01', ($by ?? $this->staff)->id);
    }

    private function fillPayload(ServerCheck $check): array
    {
        $first = $check->entries()->first();

        return ['entries' => [$first->id => ['c1' => 'OK']]];
    }

    // ---------- Membuat & mengisi: hanya Staf ----------

    public function test_hanya_staf_yang_bisa_membuat_form(): void
    {
        $this->actingAs($this->staff)->post(route('server-checks.store'), ['check_date' => '2026-09-02'])->assertRedirect();
        $this->assertDatabaseHas('server_checks', ['check_date' => '2026-09-02 00:00:00', 'created_by' => $this->staff->id]);

        $this->actingAs($this->gh)->post(route('server-checks.store'), ['check_date' => '2026-09-03'])->assertForbidden();
        $this->actingAs($this->admin)->post(route('server-checks.store'), ['check_date' => '2026-09-04'])->assertForbidden();
        $this->assertDatabaseCount('server_checks', 1);
    }

    public function test_paraf_memakai_staf_yang_mengisi(): void
    {
        $check = $this->newCheck();

        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fillPayload($check))->assertRedirect();

        $entry = $check->entries()->first()->fresh();
        $this->assertSame('OK', $entry->c1);
        $this->assertSame($this->staff->id, $entry->paraf_by);
    }

    public function test_group_head_dan_admin_tidak_bisa_mengisi_form(): void
    {
        $check = $this->newCheck();

        foreach ([$this->gh, $this->admin] as $user) {
            $this->actingAs($user)->put(route('server-checks.update', $check), $this->fillPayload($check))->assertForbidden();
        }

        $entry = $check->entries()->first()->fresh();
        $this->assertNull($entry->c1);
        $this->assertNull($entry->paraf_by);
    }

    // ---------- Tanda tangan form: hanya Group Head ----------

    public function test_hanya_group_head_yang_bisa_menandatangani(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fillPayload($check));

        $this->actingAs($this->staff)->post(route('server-checks.sign', $check))->assertForbidden();
        $this->actingAs($this->admin)->post(route('server-checks.sign', $check))->assertForbidden();
        $this->assertFalse($check->fresh()->isSigned());

        $this->actingAs($this->gh)->post(route('server-checks.sign', $check))->assertRedirect();

        $check = $check->fresh();
        $this->assertTrue($check->isSigned());
        $this->assertSame($this->gh->id, $check->signed_by);
        $this->assertSame('Group Head Satu', $check->signed_name);
        $this->assertTrue(Signature::exists($check->signed_signature));
    }

    public function test_hanya_group_head_yang_bisa_membatalkan_tanda_tangan(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fillPayload($check));
        $this->actingAs($this->gh)->post(route('server-checks.sign', $check));

        $this->actingAs($this->staff)->delete(route('server-checks.unsign', $check))->assertForbidden();
        $this->actingAs($this->admin)->delete(route('server-checks.unsign', $check))->assertForbidden();
        $this->assertTrue($check->fresh()->isSigned());

        $this->actingAs($this->gh)->delete(route('server-checks.unsign', $check))->assertRedirect();
        $this->assertFalse($check->fresh()->isSigned());
    }

    public function test_form_kosong_tidak_bisa_ditandatangani(): void
    {
        $check = $this->newCheck();

        $this->actingAs($this->gh)->post(route('server-checks.sign', $check))->assertSessionHas('error');
        $this->assertFalse($check->fresh()->isSigned());
    }

    public function test_staf_mengubah_isi_form_menghapus_tanda_tangan_group_head(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fillPayload($check));
        $this->actingAs($this->gh)->post(route('server-checks.sign', $check));
        $this->assertTrue($check->fresh()->isSigned());

        $first = $check->entries()->first();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), ['entries' => [$first->id => ['c1' => 'NOK']]]);

        $this->assertFalse($check->fresh()->isSigned());
    }

    // ---------- Blok tanda tangan: tidak ada fallback ke staf ----------

    public function test_blok_tanda_tangan_kosong_sebelum_group_head_menandatangani(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fillPayload($check));

        $so = $check->fresh()->load('entries.parafUser')->signoff;

        $this->assertFalse($so['signed']);
        $this->assertNull($so['name']);
        $this->assertNull($so['uri']);
    }

    public function test_blok_tanda_tangan_berisi_group_head_setelah_ditandatangani(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fillPayload($check));
        $this->actingAs($this->gh)->post(route('server-checks.sign', $check));

        $so = $check->fresh()->load('entries.parafUser')->signoff;

        $this->assertTrue($so['signed']);
        $this->assertSame('Group Head Satu', $so['name']);
        $this->assertStringStartsWith('data:image/png;base64,', $so['uri']);
    }

    // ---------- Tampilan ----------

    public function test_halaman_edit_sesuai_role(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fillPayload($check));

        // Staf: bisa mengisi & simpan, tidak ada tombol tanda tangan
        $this->actingAs($this->staff)->get(route('server-checks.edit', $check))
            ->assertOk()->assertSee('Simpan')->assertSee('Semua OK')
            ->assertDontSee('Approve');

        // Group Head: bisa tanda tangan, tidak bisa mengisi
        $this->actingAs($this->gh)->get(route('server-checks.edit', $check))
            ->assertOk()->assertSee('Approve')
            ->assertDontSee('Semua OK')->assertDontSee('>Simpan<', false)->assertSee('disabled', false);

        // Admin: hanya melihat
        $this->actingAs($this->admin)->get(route('server-checks.edit', $check))
            ->assertOk()->assertDontSee('Approve')->assertDontSee('Semua OK');
    }

    public function test_halaman_index_dan_pdf_tampil_untuk_semua_role(): void
    {
        $check = $this->newCheck();
        $this->actingAs($this->staff)->put(route('server-checks.update', $check), $this->fillPayload($check));

        foreach ([$this->staff, $this->gh, $this->admin] as $user) {
            $this->actingAs($user)->get(route('server-checks.index'))->assertOk();
        }

        // PDF sebelum & sesudah ditandatangani Group Head
        $this->actingAs($this->gh)->get(route('server-checks.pdf', $check))->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($this->gh)->post(route('server-checks.sign', $check));

        $this->actingAs($this->gh)->get(route('server-checks.pdf', $check))->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_halaman_profil_menjelaskan_fungsi_tanda_tangan_per_role(): void
    {
        $this->actingAs($this->staff)->get(route('profile.edit'))->assertOk()->assertSee('Paraf');
        $this->actingAs($this->gh)->get(route('profile.edit'))->assertOk()->assertSee('hanya Group Head yang menandatangani');
        $this->actingAs($this->admin)->get(route('profile.edit'))->assertOk();
    }

    // ---------- Gambar tanda tangan dipotong rapat ----------

    private function smallScribbleOnBigCanvas(bool $transparent): string
    {
        $im = imagecreatetruecolor(600, 400);

        if ($transparent) {
            imagealphablending($im, false);
            imagesavealpha($im, true);
            imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        } else {
            imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
        }

        $ink = imagecolorallocate($im, 10, 10, 120);
        imagesetthickness($im, 3);
        imageline($im, 300, 200, 330, 180, $ink);
        imageline($im, 330, 180, 350, 215, $ink);
        imageline($im, 350, 215, 380, 190, $ink);

        ob_start();
        imagepng($im);

        return ob_get_clean();
    }

    public function test_trim_memotong_margin_kosong_baik_transparan_maupun_putih(): void
    {
        foreach ([true, false] as $transparent) {
            $out = Signature::trim($this->smallScribbleOnBigCanvas($transparent));
            [$w, $h] = getimagesizefromstring($out);

            $this->assertLessThan(120, $w, 'lebar harus mengecil dari 600');
            $this->assertLessThan(60, $h, 'tinggi harus mengecil dari 400');
            $this->assertLessThanOrEqual(5.01, $w / $h, 'rasio dibatasi supaya tidak keluar kolom Paraf');
        }
    }

    public function test_trim_membiarkan_gambar_kosong(): void
    {
        $im = imagecreatetruecolor(50, 50);
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
        ob_start();
        imagepng($im);
        $blank = ob_get_clean();

        $this->assertSame($blank, Signature::trim($blank));
    }
}
