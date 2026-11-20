<?php

namespace Tests\Feature;

use App\Models\Sto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminRekapTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function user(string $username = 'admin', string $role = 'admin', bool $active = true): User
    {
        return User::create(['name' => ucfirst($username), 'username' => $username, 'password' => 'SecureExample123!', 'role' => $role, 'is_active' => $active]);
    }

    /** @param array<int, array{0:string,1:float|string,2:string,3:float}> $items [nama, qty, satuan, biaya] */
    private function sto(string $number, string $date, string $activity, array $items, string $origin = "Gudang A\nJl. Satu", string $destination = 'Gudang B', ?string $soj = 'S', ?string $mmj = 'M'): Sto
    {
        $sto = Sto::create([
            'number' => $number, 'document_date' => $date, 'activity' => $activity,
            'origin' => $origin, 'destination' => $destination, 'soj' => $soj, 'mmj' => $mmj,
            'total' => array_sum(array_column($items, 3)),
        ]);
        foreach ($items as $i => [$name, $qty, $unit, $amount]) {
            $sto->items()->create(['line' => $i + 1, 'name' => $name, 'quantity' => $qty, 'unit' => $unit, 'rate' => 1, 'amount' => $amount]);
        }

        return $sto;
    }

    /* ---------------- Peran & pengguna ---------------- */

    public function test_petugas_cannot_delete_or_open_admin_pages(): void
    {
        $sto = $this->sto('1001', '2026-11-02', 'MUAT', [['Urea', 5, 'TON', 500]]);
        $this->actingAs($this->user('budi', 'petugas'));

        $this->delete(route('sto.destroy', $sto))->assertForbidden();
        $this->assertNotSoftDeleted($sto);
        $this->get(route('sto.trash'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
        $this->post(route('users.store'), [])->assertForbidden();

        // Petugas tetap bisa input dan melengkapi STO, tanpa tombol Hapus dan menu admin.
        $this->get(route('sto.panel', $sto))->assertOk()->assertDontSee('data-delete-toggle', false);
        $this->get(route('sto.index'))->assertOk()->assertDontSee(route('sto.trash'))->assertDontSee(route('users.index'));
        $this->patch(route('sto.references', $sto), ['soj' => 'SOJ-9', 'mmj' => 'MMJ-9'])->assertRedirect();
        $this->assertSame('SOJ-9', $sto->fresh()->soj);
    }

    public function test_admin_sees_delete_and_admin_menu(): void
    {
        $sto = $this->sto('1001', '2026-11-02', 'MUAT', [['Urea', 5, 'TON', 500]]);
        $this->actingAs($this->user());
        $this->get(route('sto.panel', $sto))->assertSee('data-delete-toggle', false);
        $this->get(route('dashboard'))->assertSee(route('sto.trash'))->assertSee(route('users.index'));
        $this->delete(route('sto.destroy', $sto))->assertRedirect();
        $this->assertSoftDeleted($sto);
    }

    public function test_admin_creates_user_who_can_log_in_as_petugas(): void
    {
        $this->actingAs($this->user());
        $this->post(route('users.store'), [
            '_modal' => 'user-create', 'name' => 'Sari', 'username' => 'sari', 'role' => 'petugas',
            'password' => 'PasswordBaru123', 'password_confirmation' => 'PasswordBaru123',
        ])->assertRedirect(route('users.index'));

        $sari = User::where('username', 'sari')->first();
        $this->assertSame('petugas', $sari->role);
        $this->assertTrue($sari->is_active);

        $this->post('/logout');
        $this->post('/login', ['username' => 'sari', 'password' => 'PasswordBaru123'])->assertRedirect('/');
        $this->assertAuthenticatedAs($sari);
        $this->assertNotNull($sari->fresh()->last_login_at);
    }

    public function test_user_form_validation_is_in_indonesian(): void
    {
        $this->actingAs($this->user());
        $this->post(route('users.store'), ['_modal' => 'user-create', 'name' => '', 'username' => 'Admin Baru', 'role' => 'raja', 'password' => 'pendek', 'password_confirmation' => 'beda'])
            ->assertSessionHasErrorsIn('user', ['name', 'username', 'role', 'password']);
        $errors = session('errors')->getBag('user');
        $this->assertSame('Nama wajib diisi.', $errors->first('name'));
        $this->assertStringContainsString('huruf kecil', $errors->first('username'));
        $this->get(route('users.index'))->assertSee('data-autoopen', false)->assertSee('Nama wajib diisi.');
    }

    public function test_inactive_user_cannot_log_in_and_is_logged_out(): void
    {
        $admin = $this->user();
        $budi = $this->user('budi', 'petugas');

        $this->actingAs($admin)->put(route('users.update', $budi), ['name' => 'Budi', 'username' => 'budi', 'role' => 'petugas', 'is_active' => '0'])->assertRedirect();
        $this->assertFalse($budi->fresh()->is_active);

        // Yang sedang masuk langsung keluar pada permintaan berikutnya.
        $this->actingAs($budi->fresh())->get('/')->assertRedirect(route('login'));
        $this->assertGuest();

        $this->post('/login', ['username' => 'budi', 'password' => 'SecureExample123!'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_admin_cannot_lock_out_self_or_last_admin(): void
    {
        $admin = $this->user();
        $this->actingAs($admin);

        $this->put(route('users.update', $admin), ['name' => 'Admin', 'username' => 'admin', 'role' => 'petugas', 'is_active' => '1'])
            ->assertSessionHasErrorsIn('user', 'role');
        $this->put(route('users.update', $admin), ['name' => 'Admin', 'username' => 'admin', 'role' => 'admin', 'is_active' => '0'])
            ->assertSessionHasErrorsIn('user', 'role');
        $this->assertTrue($admin->fresh()->isAdmin() && $admin->fresh()->is_active);

        // Admin lain boleh diturunkan selama masih ada admin aktif.
        $other = $this->user('kepala');
        $this->put(route('users.update', $other), ['name' => 'Kepala', 'username' => 'kepala', 'role' => 'petugas', 'is_active' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('petugas', $other->fresh()->role);
    }

    public function test_admin_resets_password(): void
    {
        $budi = $this->user('budi', 'petugas');
        $this->actingAs($this->user())->put(route('users.password', $budi), ['password' => 'GantiBaru12345', 'password_confirmation' => 'GantiBaru12345'])->assertRedirect();
        $this->post('/logout');
        $this->post('/login', ['username' => 'budi', 'password' => 'GantiBaru12345'])->assertRedirect('/');
    }

    /* ---------------- Dashboard & rekap ---------------- */

    private function sampleData(): void
    {
        Carbon::setTestNow('2026-11-19 10:00:00');
        $this->sto('2001', '2026-11-03', 'BONGKAR', [['Urea', 19, 'TON', 1900], ['Pupuk NPK', 500, 'KG', 50]], soj: null);
        $this->sto('2002', '2026-11-10', 'MUAT', [['Urea', 10.5, 'TON', 1050]], origin: "Pelabuhan Gresik\nDermaga", destination: 'Gudang B');
        $this->sto('2003', '2026-10-20', 'MUAT', [['Urea', 7, 'TON', 700], ['Terpal', 3, 'UNIT', 30]], mmj: null);
        $this->sto('2004', '2026-11-12', 'MUAT', [['Urea', 99, 'TON', 9900]])->delete(); // di Sampah: tidak dihitung
    }

    public function test_dashboard_period_totals_and_tonnage(): void
    {
        $this->sampleData();
        $this->actingAs($this->user());

        // Bulan ini: 2 STO, nilai 3.000, tonase 19 + 0,5 + 10,5 = 30.
        $this->get(route('dashboard', ['dari' => '2026-11-01', 'sampai' => '2026-11-30']))->assertOk()
            ->assertViewHas('total', 2)
            ->assertViewHas('amount', 3000.0)
            ->assertViewHas('tonnage', 30.0)
            ->assertViewHas('bongkar', 1)
            ->assertViewHas('incomplete', 2) // kelengkapan selalu seluruh STO
            ->assertViewHas('chart', fn ($c) => $c['daily'] && count($c['bars']) === 30 && $c['bars'][2]['ton'] == 19.5)
            ->assertSee('Bulan ini (1 Nov 2026 – 30 Nov 2026)')
            ->assertSee('SOJ kosong 1 · MMJ kosong 1');

        // Semua periode: per bulan; baris UNIT tidak masuk tonase dan disebutkan.
        $this->get(route('dashboard'))->assertOk()
            ->assertViewHas('total', 3)
            ->assertViewHas('tonnage', 37.0)
            ->assertViewHas('otherUnitRows', 1)
            ->assertViewHas('chart', fn ($c) => ! $c['daily'] && count($c['bars']) === 2 && $c['bars'][1]['stos'] === 2)
            ->assertSee('1 baris bersatuan lain tidak dihitung');
    }

    public function test_dashboard_uses_few_queries(): void
    {
        $this->sampleData();
        $this->actingAs($this->user());
        DB::enableQueryLog();
        $this->get(route('dashboard'))->assertOk();
        $this->assertLessThanOrEqual(6, count(DB::getQueryLog()), implode("\n", array_column(DB::getQueryLog(), 'query')));
    }

    public function test_rekap_groups_by_month_item_activity_and_route(): void
    {
        $this->sampleData();
        $this->actingAs($this->user('budi', 'petugas'));

        $res = $this->get(route('rekap'))->assertOk();
        $res->assertViewHas('summary', ['stos' => 3, 'ton' => 37.0, 'amount' => 3730.0]);
        $res->assertViewHas('byMonth', fn ($m) => array_keys($m) === ['2026-10', '2026-11'] && $m['2026-11']['ton'] == 30.0 && $m['2026-11']['label'] === 'November 2026');
        $res->assertViewHas('byActivity', fn ($a) => $a['BONGKAR']['stos'] === 1 && $a['MUAT']['amount'] == 1780.0);
        $res->assertViewHas('byRoute', fn ($r) => isset($r['Gudang A → Gudang B'], $r['Pelabuhan Gresik → Gudang B']) && $r['Gudang A → Gudang B']['stos'] === 2);
        $res->assertViewHas('byItem', fn ($i) => $i->firstWhere('name', 'Urea')->stos == 3 && (float) $i->firstWhere('name', 'Urea')->qty == 36.5);
        $res->assertSee('19,5')->assertSee('Gudang A → Gudang B');

        // Filter periode
        $this->get(route('rekap', ['dari' => '2026-10-01', 'sampai' => '2026-10-31']))
            ->assertViewHas('summary', ['stos' => 1, 'ton' => 7.0, 'amount' => 730.0]);
        $this->get(route('rekap', ['dari' => '2025-01-01', 'sampai' => '2025-01-31']))->assertSee('Tidak ada STO pada periode ini');
    }
}
