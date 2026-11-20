<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\{User,ImportBatch,Sto};
use App\Services\StoPdfParser;
class StoFlowTest extends TestCase {
 use RefreshDatabase;
 private function user(string $name='admin', string $role='admin'): User {return User::create(['name'=>$name,'username'=>$name,'email'=>$name.'@example.com','password'=>'SecureExample123!','role'=>$role,'is_active'=>true]);}
 private function batch(User $user): ImportBatch {return ImportBatch::create(['user_id'=>$user->id,'original_name'=>'test.pdf','path'=>'sto/test.pdf','sha256'=>str_repeat('a',64),'warnings'=>[],'payload'=>[$this->row(),$this->row()],'expires_at'=>now()->addDay()]);}
 private function row(): array {return ['number'=>'1234567890','document_date'=>'2026-01-05','activity'=>'BONGKAR','origin'=>'Gresik','destination'=>'Malang','total'=>'200.00','source_page'=>1,'notes'=>[],'items'=>[['line'=>1,'name'=>'Urea','quantity'=>'2.000','unit'=>'TON','rate'=>'100.00','amount'=>'200.00']]];}
 public function test_guest_cannot_access_master_or_pdf(): void {$this->get('/sto')->assertRedirect('/login');$this->get('/sto/1/pdf')->assertRedirect('/login');}
 public function test_login_and_logout(): void {$u=$this->user();$this->post('/login',['username'=>$u->username,'password'=>'SecureExample123!'])->assertRedirect('/');$this->assertAuthenticatedAs($u);$this->post('/logout')->assertRedirect('/login');$this->assertGuest();}
 public function test_login_throttles_wrong_passwords(): void {$u=$this->user();for($i=0;$i<6;$i++)$this->post('/login',['username'=>$u->username,'password'=>'wrong'])->assertSessionHasErrors('username');$this->assertGuest();}
 public function test_upload_is_preview_only(): void {
  Storage::fake('local');$u=$this->user();$this->mock(StoPdfParser::class,fn($m)=>$m->shouldReceive('parse')->once()->andReturn(['rows'=>[$this->row()],'warnings'=>[]]));
  $this->actingAs($u)->post('/sto/upload',['pdf'=>UploadedFile::fake()->createWithContent('test.pdf',"%PDF-1.4\n%%EOF")])->assertRedirect();$this->assertDatabaseCount('stos',0);$this->assertDatabaseCount('import_batches',1);
 }
 public function test_duplicate_commit_and_repeat_submission(): void {
  $u=$this->user();$batch=$this->batch($u);$this->actingAs($u)->get(route('sto.preview',$batch))->assertOk()->assertSee('Duplikat');
  $this->post(route('sto.commit',$batch))->assertRedirect(route('sto.index'));$this->assertDatabaseCount('stos',1);$this->assertDatabaseCount('sto_items',1);
  $this->post(route('sto.commit',$batch))->assertStatus(410);$batch2=$this->batch($u);$this->post(route('sto.commit',$batch2))->assertRedirect();$this->assertDatabaseCount('stos',1);
 }
 public function test_cannot_commit_another_users_preview(): void {$owner=$this->user();$batch=$this->batch($owner);$this->actingAs($this->user('other'))->post(route('sto.commit',$batch))->assertForbidden();}
 public function test_expired_preview_cannot_be_saved(): void {$u=$this->user();$batch=$this->batch($u);$batch->update(['expires_at'=>now()->subMinute()]);$this->actingAs($u)->post(route('sto.commit',$batch))->assertStatus(410);$this->assertDatabaseCount('stos',0);}
 public function test_dashboard_list_and_details_render(): void {$u=$this->user();$b=$this->batch($u);$this->actingAs($u)->post(route('sto.commit',$b));$this->get('/')->assertOk()->assertSee('1234567890');$this->get('/sto')->assertOk()->assertSee('Urea');$this->get(route('sto.show',Sto::first()))->assertRedirect(route('sto.index',['open'=>Sto::first()->id]));$this->get(route('sto.index',['open'=>Sto::first()->id]))->assertOk()->assertSee('id="sto-'.Sto::first()->id.'"',false);}
 public function test_cancel_removes_private_pdf(): void {Storage::fake('local');$u=$this->user();$b=$this->batch($u);Storage::put($b->path,'pdf');$this->actingAs($u)->delete(route('sto.cancel',$b))->assertRedirect();Storage::assertMissing($b->path);$this->assertDatabaseCount('import_batches',0);}
 public function test_preview_is_rendered_as_modal_on_master_page(): void {
  $u=$this->user();$batch=$this->batch($u);
  $this->actingAs($u)->get(route('sto.preview',$batch))->assertOk()->assertSee('id="preview-modal"',false)->assertSee('Simpan 1 STO')->assertSee('Master STO');
  $this->get(route('sto.index'))->assertOk()->assertDontSee('id="preview-modal"',false);
 }

 private function payload(array $override=[]): array {
  return array_replace([
   'number'=>'5320099001','document_date'=>'2026-10-05','activity'=>'MUAT','origin'=>"GD PKT PADIMAS 1\nGresik",'destination'=>"GD PKT LAMPUNG\nLampung",
   'soj'=>'','mmj'=>'',
   'items'=>[['name'=>'NPK 14-12-16','quantity'=>'84.6','unit'=>'ton','rate'=>'13100'],['name'=>'Urea','quantity'=>'10.125','unit'=>'TON','rate'=>'1000.50']],
  ],$override);
 }
 private function commitedSto(): array {$u=$this->user();$b=$this->batch($u);$this->actingAs($u)->post(route('sto.commit',$b));return [$u,Sto::firstOrFail()];}
 public function test_no_verification_or_usage_columns_remain(): void {
  $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('stos','used'));
  $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('stos','verified_at'));
  $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('stos','verified_by'));
  $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('stos','soj'));$this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('stos','mmj'));
  $this->assertFalse(\Illuminate\Support\Facades\Route::has('sto.verify'));$this->assertFalse(\Illuminate\Support\Facades\Route::has('sto.usage'));
 }
 public function test_imported_sto_has_empty_soj_mmj_and_modal_warns(): void {
  [$u,$sto]=$this->commitedSto();$this->assertNull($sto->soj);$this->assertNull($sto->mmj);
  $this->get(route('sto.index'))->assertOk()->assertSee('1 belum lengkap')->assertDontSee('Verifikasi')->assertDontSee('Pemakaian');
  $this->get(route('sto.panel',$sto))->assertOk()->assertSee('SOJ &amp; MMJ kosong',false)->assertDontSee('Belum diisi')->assertSee('id="sto-'.$sto->id.'"',false);
  $this->get('/')->assertOk()->assertSee('SOJ kosong 1 · MMJ kosong 1')->assertDontSee('STO belum lengkap.')->assertDontSee('Verifikasi')->assertDontSee('Pemakaian');
 }
 public function test_soj_and_mmj_can_be_filled_and_warning_updates(): void {
  [$u,$sto]=$this->commitedSto();
  $this->patch(route('sto.references',$sto),['soj'=>'SOJ-001','mmj'=>'','_modal'=>'sto-'.$sto->id,'_form'=>'references'])->assertRedirect()->assertSessionHas('reopen','sto-'.$sto->id);
  $this->assertSame('SOJ-001',$sto->fresh()->soj);$this->assertNull($sto->fresh()->mmj);
  $this->get(route('sto.panel',$sto))->assertSee('MMJ kosong</span>',false)->assertDontSee('SOJ &amp; MMJ kosong',false);
  $this->patch(route('sto.references',$sto),['soj'=>' SOJ-001 ','mmj'=>'MMJ-77'])->assertRedirect();
  $this->assertSame('MMJ-77',$sto->fresh()->mmj);$this->assertSame([],$sto->fresh()->missingReferences());
  $this->get(route('sto.index'))->assertDontSee('belum lengkap</span>',false);
 }
 public function test_references_reject_too_long_values_in_sto_bag(): void {
  [$u,$sto]=$this->commitedSto();
  $this->patch(route('sto.references',$sto),['soj'=>str_repeat('A',101)])->assertSessionHasErrors('soj',null,'sto');$this->assertNull($sto->fresh()->soj);
 }
 public function test_sto_can_be_created_manually_with_computed_totals(): void {
  $u=$this->user();
  $this->actingAs($u)->post(route('sto.store'),$this->payload())->assertRedirect(route('sto.index'));
  $sto=Sto::with('items')->firstOrFail();
  $this->assertSame('5320099001',$sto->number);$this->assertNull($sto->batch);$this->assertNull($sto->source_page);$this->assertNull($sto->soj);
  $this->assertCount(2,$sto->items);$this->assertSame('TON',$sto->items[0]->unit);
  $this->assertSame('1108260.00',(string)$sto->items[0]->amount);   // 84,6 x 13.100
  $this->assertSame('10130.06',(string)$sto->items[1]->amount);     // 10,125 x 1.000,50 = 10.130,0625 dibulatkan
  $this->assertSame('1118390.06',(string)$sto->total);
  $this->get(route('sto.index'))->assertOk()->assertSee('5320099001')->assertSee('Input manual');
 }
 public function test_create_validation_errors_use_sto_bag_and_keep_input(): void {
  $u=$this->user();
  $this->actingAs($u)->from(route('sto.index'))->post(route('sto.store'),$this->payload(['number'=>'','activity'=>'X','items'=>[['name'=>'','quantity'=>'0','unit'=>'','rate'=>'-1']],'_modal'=>'create-sto','_form'=>'create']))
   ->assertRedirect(route('sto.index'))->assertSessionHasErrors(['number','activity','items.0.name','items.0.quantity','items.0.unit','items.0.rate'],null,'sto')->assertSessionHasInput('_modal','create-sto');
  $this->assertDatabaseCount('stos',0);
  $this->post(route('sto.store'),$this->payload(['items'=>[]]))->assertSessionHasErrors('items',null,'sto');
  $this->post(route('sto.store'),$this->payload(['items'=>[['name'=>'A','quantity'=>'1.2345','unit'=>'TON','rate'=>'1']]]))->assertSessionHasErrors('items.0.quantity',null,'sto');
  $this->post(route('sto.store'),$this->payload(['number'=>'bad number!']))->assertSessionHasErrors('number',null,'sto');
  $this->post(route('sto.store'),$this->payload(['document_date'=>'31/12/2026']))->assertSessionHasErrors('document_date',null,'sto');
 }
 public function test_duplicate_number_is_rejected_on_create_and_update_but_own_number_is_allowed(): void {
  $u=$this->user();$this->actingAs($u)->post(route('sto.store'),$this->payload());
  $this->post(route('sto.store'),$this->payload())->assertSessionHasErrors('number',null,'sto');$this->assertDatabaseCount('stos',1);
  $this->post(route('sto.store'),$this->payload(['number'=>'5320099002']));
  $first=Sto::where('number','5320099001')->firstOrFail();
  $this->put(route('sto.update',$first),$this->payload(['number'=>'5320099002']))->assertSessionHasErrors('number',null,'sto');
  $this->put(route('sto.update',$first),$this->payload(['activity'=>'BONGKAR']))->assertSessionHasNoErrors();$this->assertSame('BONGKAR',$first->fresh()->activity);
 }
 public function test_sto_can_be_edited_including_items_and_soj_mmj(): void {
  [$u,$sto]=$this->commitedSto();
  $this->put(route('sto.update',$sto),$this->payload(['number'=>'5320099555','soj'=>'S-1','mmj'=>'M-1','items'=>[['name'=>'Pupuk Z','quantity'=>'2','unit'=>'kg','rate'=>'250.25']]]))->assertRedirect()->assertSessionHas('reopen','sto-'.$sto->id);
  $sto=$sto->fresh()->load('items');
  $this->assertSame('5320099555',$sto->number);$this->assertSame('S-1',$sto->soj);$this->assertSame('M-1',$sto->mmj);
  $this->assertCount(1,$sto->items);$this->assertSame(1,$sto->items[0]->line);$this->assertSame('KG',$sto->items[0]->unit);
  $this->assertSame('500.50',(string)$sto->items[0]->amount);$this->assertSame('500.50',(string)$sto->total);
  $this->assertNotNull($sto->import_batch_id);$this->assertSame(1,(int)$sto->source_page); // asal PDF tetap
  $this->assertDatabaseCount('sto_items',1);
 }
 public function test_delete_moves_to_trash_and_can_be_restored_or_purged(): void {
  [$u,$sto]=$this->commitedSto();
  $this->delete(route('sto.destroy',$sto))->assertRedirect(route('sto.index'))->assertSessionHas('undo');
  $this->assertSoftDeleted('stos',['id'=>$sto->id,'deleted_by'=>$u->id]);$this->assertDatabaseCount('sto_items',1);
  $this->get(route('sto.index')); // habiskan notifikasi "dipindah ke Sampah"
  $this->get(route('sto.index'))->assertDontSee($sto->number);
  $this->get(route('sto.trash'))->assertOk()->assertSee($sto->number)->assertSee($u->name);
  $this->post(route('sto.restore',$sto->id))->assertRedirect(route('sto.index',['open'=>$sto->id]));
  $this->assertNotSoftDeleted('stos',['id'=>$sto->id]);$this->assertNull($sto->fresh()->deleted_by);
  // hapus permanen hanya dari Sampah
  $this->delete(route('sto.force-destroy',$sto->id))->assertNotFound();
  $this->delete(route('sto.destroy',$sto));
  $this->delete(route('sto.force-destroy',$sto->id))->assertRedirect(route('sto.trash'));
  $this->assertDatabaseCount('stos',0);$this->assertDatabaseCount('sto_items',0);$this->assertDatabaseCount('import_batches',1);
  // riwayat tetap ada walau STO sudah dihapus permanen
  $this->assertSame(['imported','deleted','restored','deleted','purged'],\App\Models\StoActivity::orderBy('id')->pluck('action')->all());
  $this->assertSame(0,\App\Models\StoActivity::whereNotNull('sto_id')->count());
 }
 public function test_number_in_trash_blocks_manual_create_and_is_skipped_on_import(): void {
  [$u,$sto]=$this->commitedSto();$this->delete(route('sto.destroy',$sto));
  $this->post(route('sto.store'),$this->payload(['number'=>$sto->number]))->assertSessionHasErrors(['number'=>'Nomor STO ini ada di Sampah. Pulihkan dari menu Sampah, atau hapus permanen dulu.'],null,'sto');
  $b=$this->batch($u);$this->get(route('sto.preview',$b))->assertSee('Ada di Sampah');
  $this->post(route('sto.commit',$b))->assertSessionHas('success','0 STO disimpan. 2 duplikat dilewati.');
  $this->assertSame(1,Sto::withTrashed()->count());
 }
 public function test_changes_are_recorded_with_user_and_old_new_values(): void {
  [$u,$sto]=$this->commitedSto();
  $this->assertSame($u->id,(int)$sto->created_by);
  $this->patch(route('sto.references',$sto),['soj'=>'SOJ-1','mmj'=>'','_modal'=>'sto-'.$sto->id,'_form'=>'references']);
  $this->patch(route('sto.references',$sto),['soj'=>'SOJ-1','mmj'=>'']); // tanpa perubahan: tidak dicatat
  $log=\App\Models\StoActivity::where('action','references')->sole();
  $this->assertSame(['soj'=>[null,'SOJ-1']],$log->changes);$this->assertSame($u->name,$log->user_name);
  $this->assertSame($u->id,(int)$sto->fresh()->updated_by);
  $this->put(route('sto.update',$sto),$this->payload(['number'=>$sto->number,'soj'=>'SOJ-1','origin'=>'Gresik Baru','items'=>[['name'=>'Urea','quantity'=>'3','unit'=>'TON','rate'=>'100']]]));
  $upd=\App\Models\StoActivity::where('action','updated')->sole()->changes;
  $this->assertSame(['Gresik','Gresik Baru'],$upd['origin']);$this->assertSame(['200.00','300.00'],$upd['total']);$this->assertArrayHasKey('items',$upd);
  $this->get(route('sto.index',['open'=>$sto->id]))->assertSee('Riwayat')->assertSee('SOJ/MMJ diisi')->assertSee('Gresik Baru');
 }
 public function test_search_and_completeness_filters(): void {
  $u=$this->user();$this->actingAs($u);
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000001','soj'=>'SOJ-AAA','mmj'=>'MMJ-AAA']));
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000002','soj'=>'SOJ-BBB']));
  $this->get(route('sto.index')); // habiskan flash "reopen" seperti redirect di browser
  $this->get(route('sto.index',['completeness'=>'complete']))->assertSee('5320000001')->assertDontSee('5320000002');
  $this->get(route('sto.index',['completeness'=>'incomplete']))->assertSee('5320000002')->assertDontSee('5320000001');
  $this->get(route('sto.index',['q'=>'SOJ-BBB']))->assertSee('5320000002')->assertDontSee('5320000001');
 }
 private function sheetXml($response): string {
  $file=$response->getFile()->getPathname();$zip=new \ZipArchive();$this->assertTrue($zip->open($file)===true);
  $xml=$zip->getFromName('xl/worksheets/sheet1.xml');$zip->close();$this->assertNotFalse($xml);return $xml;
 }
 public function test_excel_export_keeps_real_numbers_and_follows_filters(): void {
  $u=$this->user();$this->actingAs($u);
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000001','soj'=>'SOJ-X','mmj'=>'MMJ-Y']));
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000002']));
  $res=$this->get(route('sto.export'))->assertOk()->assertHeader('content-type','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  $xml=$this->sheetXml($res);
  $this->assertStringContainsString('5320000001',$xml);$this->assertStringContainsString('5320000002',$xml);
  $this->assertStringContainsString('<v>84.6</v>',$xml);     // Qty 84,6 ton tersimpan sebagai angka, bukan "84.600"
  $this->assertStringContainsString('<v>10.125</v>',$xml);
  $this->assertStringContainsString('<v>1000.5</v>',$xml);   // tarif
  $this->assertStringContainsString('SOJ-X',$xml);$this->assertStringNotContainsString('Verifikasi',$xml);
  // filter "belum lengkap": hanya STO kedua
  $xml=$this->sheetXml($this->get(route('sto.export',['completeness'=>'incomplete'])));
  $this->assertStringContainsString('5320000002',$xml);$this->assertStringNotContainsString('5320000001',$xml);
  // Total STO hanya di baris barang pertama (2 barang -> 1 total) agar kolom bisa dijumlah
  $this->assertSame(1,preg_match_all('/<c r="N\d+"/',$xml) - 1);      // 1 STO -> tepat 1 sel Total STO (dikurangi judul)
  $this->assertSame(2,preg_match_all('/<c r="J\d+"/',$xml) - 1);       // 2 baris barang (dikurangi judul)
  $this->assertStringContainsString('<v>1118390.06</v>',$xml);        // 84,6×13.100 + 10,125×1.000,50
 }
 public function test_manual_sto_has_no_pdf_and_guests_cannot_write(): void {
  $u=$this->user();$this->actingAs($u)->post(route('sto.store'),$this->payload());$sto=Sto::firstOrFail();
  $this->get(route('sto.pdf',$sto))->assertNotFound();
  auth()->logout();
  $this->post(route('sto.store'),$this->payload())->assertRedirect('/login');
  $this->put(route('sto.update',$sto),$this->payload())->assertRedirect('/login');
  $this->patch(route('sto.references',$sto),['soj'=>'x'])->assertRedirect('/login');
  $this->delete(route('sto.destroy',$sto))->assertRedirect('/login');
 }
 public function test_messages_are_in_indonesian(): void {
  $u=$this->user();
  $this->post('/login',['username'=>'','password'=>''])->assertSessionHasErrors(['username'=>'Username wajib diisi.']);
  $this->actingAs($u)->post('/sto/upload',[])->assertSessionHasErrors(['pdf'=>'Pilih file PDF terlebih dahulu.']);
  $this->put(route('password.update'),['current_password'=>'salah','password'=>'pendek','password_confirmation'=>'lain'])
   ->assertSessionHasErrors(['current_password'=>'Password saat ini salah.']);
  $this->put(route('password.update'),['current_password'=>'SecureExample123!','password'=>'pendek','password_confirmation'=>'pendek'])
   ->assertSessionHasErrors(['password'=>'Password baru minimal 12 karakter.']);
 }
 public function test_commit_message_omits_zero_duplicates(): void {
  $u=$this->user();$b=ImportBatch::create(['user_id'=>$u->id,'original_name'=>'t.pdf','path'=>'sto/t.pdf','sha256'=>str_repeat('a',64),'warnings'=>[],'payload'=>[$this->row()],'expires_at'=>now()->addDay()]);
  $this->actingAs($u)->post(route('sto.commit',$b))->assertSessionHas('success','1 STO disimpan.');
 }
 public function test_preview_cancel_needs_confirmation(): void {
  $u=$this->user();$b=$this->batch($u);
  $html=$this->actingAs($u)->get(route('sto.preview',$b))->assertOk()->getContent();
  $this->assertStringContainsString('aria-label="Tutup dan batalkan impor"',$html);
  $this->assertStringNotContainsString('form="cancel-form"',$html);                 // tombol X tidak lagi langsung mengirim batal
  $this->assertMatchesRegularExpression('/class="foot-confirm".*?id="cancel-form"/s',$html); // form batal hanya di area konfirmasi
  $this->assertStringContainsString('Batalkan impor?',$html);
 }
 public function test_expired_previews_and_orphan_files_are_cleaned_without_scheduler(): void {
  Storage::fake('local');$u=$this->user();
  $old=$this->batch($u);$old->update(['path'=>'sto/old.pdf','expires_at'=>now()->subHour()]);Storage::put('sto/old.pdf','x');
  $live=$this->batch($u);$live->update(['path'=>'sto/live.pdf']);Storage::put('sto/live.pdf','x');
  Storage::put('sto/orphan.pdf','x');touch(Storage::path('sto/orphan.pdf'),now()->subDays(2)->timestamp);
  Storage::put('sto/fresh-orphan.pdf','x');
  \Illuminate\Support\Facades\Cache::forget('sto-import-cleanup');
  $this->actingAs($u)->get(route('sto.index'))->assertOk();   // cukup membuka Master STO
  $this->assertModelMissing($old);Storage::assertMissing('sto/old.pdf');Storage::assertMissing('sto/orphan.pdf');
  $this->assertModelExists($live);Storage::assertExists('sto/live.pdf');Storage::assertExists('sto/fresh-orphan.pdf');
 }
 public function test_master_page_renders_detail_only_on_demand(): void {
  $u=$this->user();$this->actingAs($u);
  foreach(['5320000001','5320000002','5320000003'] as $n) $this->post(route('sto.store'),$this->payload(['number'=>$n]));
  $this->get(route('sto.index'));
  $html=$this->get(route('sto.index'))->assertOk()->getContent();
  $this->assertSame(0,preg_match_all('/<dialog[^>]+id="sto-\d+"/',$html));          // tidak ada jendela detail yang ikut dirender
  $this->assertStringContainsString('id="sto-panel-url"',$html);
  $id=Sto::where('number','5320000002')->value('id');
  $this->get(route('sto.panel',$id))->assertOk()->assertSee('5320000002')->assertSee('form-sto-'.$id,false);
  $this->assertSame(1,preg_match_all('/<dialog[^>]+id="sto-\d+"/',$this->get(route('sto.index',['open'=>$id]))->getContent()));
  auth()->logout();$this->get(route('sto.panel',$id))->assertRedirect('/login');
 }
 public function test_quick_fill_saves_only_changed_fields_and_skips_conflicts(): void {
  $u=$this->user();$this->actingAs($u);
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000001']));
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000002','mmj'=>'MMJ-LAMA']));
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000003']));
  [$a,$b,$c]=Sto::orderBy('number')->get()->all();
  $this->get(route('sto.index'));
  $this->get(route('sto.index',['mode'=>'isi']))->assertOk()->assertSee('Simpan semua')->assertSee('name="refs['.$a->id.'][soj]"',false);
  $c->update(['soj'=>'DIUBAH-ORANG-LAIN']);   // berubah setelah halaman dibuka
  $this->from(route('sto.index',['mode'=>'isi']))->patch(route('sto.references.bulk'),['refs'=>[
   $a->id=>['soj'=>'SOJ-A','soj_awal'=>'','mmj'=>'MMJ-A','mmj_awal'=>''],
   $b->id=>['soj'=>'','soj_awal'=>'','mmj'=>'MMJ-LAMA','mmj_awal'=>'MMJ-LAMA'],   // tidak diubah
   $c->id=>['soj'=>'SOJ-C','soj_awal'=>'','mmj'=>'','mmj_awal'=>''],
  ]])->assertRedirect(route('sto.index',['mode'=>'isi']))->assertSessionHas('success','SOJ/MMJ 1 STO disimpan. 1 STO dilewati karena sudah diubah orang lain: 5320000003. Periksa lagi.');
  $this->assertSame(['SOJ-A','MMJ-A'],[$a->fresh()->soj,$a->fresh()->mmj]);
  $this->assertSame('DIUBAH-ORANG-LAIN',$c->fresh()->soj);
  $this->assertSame(1,\App\Models\StoActivity::where('action','references')->count());
  $this->assertSame('Isi cepat',\App\Models\StoActivity::where('action','references')->value('note'));
 }
 private function failedRow(): array {return ['number'=>'7770000001','source_page'=>4,'activity'=>'MUAT','origin'=>'Gresik','destination'=>"Gudang St. Mary's <b>x</b>",'items'=>[['name'=>'Urea','quantity'=>'5.000','unit'=>'TON','rate'=>'']],'message'=>'Tanggal dokumen tidak valid.'];}
 public function test_failed_rows_can_be_added_manually_with_pdf_link(): void {
  Storage::fake('local');$u=$this->user();
  $this->mock(StoPdfParser::class,fn($m)=>$m->shouldReceive('parse')->once()->andReturn(['rows'=>[],'warnings'=>['x'],'failed'=>[$this->failedRow()]]));
  $this->actingAs($u)->post('/sto/upload',['pdf'=>UploadedFile::fake()->createWithContent('gagal.pdf',"%PDF-1.4\n%%EOF")]);
  $batch=ImportBatch::firstOrFail();$this->assertCount(1,$batch->failed);
  $this->get(route('sto.preview',$batch))->assertOk()->assertSee('1 STO gagal dibaca')->assertSee('Lanjut ke tambah manual');
  $this->post(route('sto.commit',$batch))->assertSessionHas('success','0 STO disimpan. 1 STO gagal dibaca bisa ditambahkan manual.');
  $this->get(route('sto.index'));
  $html=$this->get(route('sto.index'))->assertSee('1 STO gagal dibaca, perlu ditambahkan manual')->assertSee('7770000001')->getContent();
  $this->assertStringContainsString('data-prefill=',$html);$this->assertStringContainsString('&quot;origin&quot;:&quot;Gresik&quot;',$html);
  $this->assertStringNotContainsString("Mary's <b>",$html);$this->assertStringContainsString('Mary&#039;s &lt;b&gt;x&lt;/b&gt;',$html); // teks dari PDF di-escape
  $this->get(route('sto.batch-pdf',$batch))->assertOk();
  // orang lain tidak boleh menautkan ke PDF impor ini
  $other=$this->user('other');
  $this->actingAs($other)->post(route('sto.store'),$this->payload(['number'=>'7770000001','import_batch_id'=>$batch->id,'source_page'=>4]))->assertSessionHasErrors(['import_batch_id'],null,'sto');
  $this->get(route('sto.batch-pdf',$batch))->assertForbidden();
  // pemilik menambah manual -> tertaut ke PDF dan hilang dari daftar gagal
  $this->actingAs($u)->post(route('sto.store'),$this->payload(['number'=>'7770000001','import_batch_id'=>$batch->id,'source_page'=>4]))->assertSessionHasNoErrors();
  $sto=Sto::where('number','7770000001')->firstOrFail();$this->assertSame($batch->id,(int)$sto->import_batch_id);$this->assertSame(4,(int)$sto->source_page);
  $this->get(route('sto.index'));$this->get(route('sto.index'))->assertDontSee('perlu ditambahkan manual');
 }
 public function test_failed_list_can_be_dismissed(): void {
  $u=$this->user();$b=ImportBatch::create(['user_id'=>$u->id,'original_name'=>'t.pdf','path'=>'sto/t.pdf','sha256'=>str_repeat('b',64),'warnings'=>[],'payload'=>[],'failed'=>[$this->failedRow()],'status'=>'committed','expires_at'=>now()->addDay()]);
  $this->actingAs($u)->get(route('sto.index'))->assertSee('perlu ditambahkan manual');
  $this->post(route('sto.failed.dismiss',$b))->assertRedirect();$this->assertNull($b->fresh()->failed);
  $this->actingAs($this->user('other'))->post(route('sto.failed.dismiss',$b))->assertForbidden();
 }
 public function test_same_pdf_is_detected_before_reading_again(): void {
  Storage::fake('local');$u=$this->user();
  $this->mock(StoPdfParser::class,fn($m)=>$m->shouldReceive('parse')->twice()->andReturn(['rows'=>[$this->row()],'warnings'=>[],'failed'=>[]]));
  $pdf=fn()=>UploadedFile::fake()->createWithContent('sama.pdf',"%PDF-1.4 isi sama\n%%EOF");
  $this->actingAs($u)->post('/sto/upload',['pdf'=>$pdf()]);$first=ImportBatch::firstOrFail();
  // masih dalam pratinjau -> langsung ke pratinjau yang sama, tidak dibaca ulang
  $this->post('/sto/upload',['pdf'=>$pdf()])->assertRedirect(route('sto.preview',$first));$this->assertSame(1,ImportBatch::count());
  $this->post(route('sto.commit',$first));
  // sudah diimpor -> ditahan, muncul peringatan
  $this->post('/sto/upload',['pdf'=>$pdf()])->assertRedirect(route('sto.index',['unggah'=>1]))->assertSessionHas('pdf_duplicate');
  $this->assertSame(1,ImportBatch::count());
  $this->get(route('sto.index',['unggah'=>1]))->assertSee('PDF ini sudah pernah diimpor.')->assertSee('(1 STO)');
  $held=session('pdf_duplicate')['path'];Storage::assertExists($held);
  // tetap baca -> pratinjau baru
  $this->post(route('sto.upload.anyway'))->assertRedirect();$this->assertSame(2,ImportBatch::count());$this->assertNull(session('pdf_duplicate'));
  $this->post(route('sto.upload.anyway'))->assertStatus(410);
  // batal -> file ditahan dihapus
  $this->post('/sto/upload',['pdf'=>$pdf()]);   // pratinjau kedua masih aktif -> diarahkan ke sana
  ImportBatch::where('status','preview')->update(['status'=>'committed']);
  $this->post('/sto/upload',['pdf'=>$pdf()])->assertSessionHas('pdf_duplicate');$held=session('pdf_duplicate')['path'];
  $this->post(route('sto.upload.discard'))->assertRedirect(route('sto.index'));Storage::assertMissing($held);
 }
 public function test_search_by_item_period_filter_and_sort(): void {
  $u=$this->user();$this->actingAs($u);
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000001','document_date'=>'2026-09-10','items'=>[['name'=>'ZA Plus','quantity'=>'1','unit'=>'TON','rate'=>'10']]]));
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000002','document_date'=>'2026-10-05']));
  $this->post(route('sto.store'),$this->payload(['number'=>'5320000003','document_date'=>'2026-10-20']));
  $this->get(route('sto.index'));
  $this->get(route('sto.index',['q'=>'za plus']))->assertSee('5320000001')->assertDontSee('5320000002');
  $this->get(route('sto.index',['dari'=>'2026-10-01','sampai'=>'2026-10-31']))->assertSee('5320000002')->assertSee('5320000003')->assertDontSee('5320000001');
  $this->get(route('sto.index',['dari'=>'bukan-tanggal']))->assertOk()->assertSee('5320000001');
  $html=$this->get(route('sto.index',['urut'=>'tanggal_asc']))->getContent();
  $this->assertTrue(strpos($html,'5320000001')<strpos($html,'5320000003'));
  $html=$this->get(route('sto.index'))->getContent();   // bawaan: tanggal STO terbaru
  $this->assertTrue(strpos($html,'5320000003')<strpos($html,'5320000001'));
  $xml=$this->sheetXml($this->get(route('sto.export',['dari'=>'2026-10-01','sampai'=>'2026-10-31','urut'=>'nomor_asc'])));
  $this->assertStringNotContainsString('5320000001',$xml);$this->assertTrue(strpos($xml,'5320000002')<strpos($xml,'5320000003'));
 }
}
