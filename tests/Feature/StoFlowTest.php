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
 private function user(string $name='admin'): User {return User::create(['name'=>$name,'username'=>$name,'email'=>$name.'@example.com','password'=>'SecureExample123!']);}
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
  $this->get(route('sto.index'))->assertOk()->assertSee('SOJ dan MMJ masih kosong.')->assertSee('1 STO belum lengkap.')->assertDontSee('Verifikasi')->assertDontSee('Pemakaian');
  $this->get('/')->assertOk()->assertSee('1 STO belum lengkap.')->assertDontSee('Verifikasi')->assertDontSee('Pemakaian');
 }
 public function test_soj_and_mmj_can_be_filled_and_warning_updates(): void {
  [$u,$sto]=$this->commitedSto();
  $this->patch(route('sto.references',$sto),['soj'=>'SOJ-001','mmj'=>'','_modal'=>'sto-'.$sto->id,'_form'=>'references'])->assertRedirect()->assertSessionHas('reopen','sto-'.$sto->id);
  $this->assertSame('SOJ-001',$sto->fresh()->soj);$this->assertNull($sto->fresh()->mmj);
  $this->get(route('sto.index'))->assertSee('MMJ masih kosong.')->assertDontSee('SOJ dan MMJ masih kosong.');
  $this->patch(route('sto.references',$sto),['soj'=>' SOJ-001 ','mmj'=>'MMJ-77'])->assertRedirect();
  $this->assertSame('MMJ-77',$sto->fresh()->mmj);$this->assertSame([],$sto->fresh()->missingReferences());
  $this->get(route('sto.index'))->assertDontSee('1 STO belum lengkap.');
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
 public function test_sto_can_be_deleted_with_items_cascade(): void {
  [$u,$sto]=$this->commitedSto();
  $this->delete(route('sto.destroy',$sto))->assertRedirect(route('sto.index'));
  $this->assertDatabaseCount('stos',0);$this->assertDatabaseCount('sto_items',0);$this->assertDatabaseCount('import_batches',1);
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
 public function test_csv_export_has_soj_mmj_and_no_status_columns(): void {
  $u=$this->user();$this->actingAs($u)->post(route('sto.store'),$this->payload(['soj'=>'SOJ-X','mmj'=>'MMJ-Y']));
  $csv=$this->get(route('sto.export'))->assertOk()->streamedContent();
  $this->assertStringContainsString('SOJ;MMJ',$csv);$this->assertStringContainsString('SOJ-X;MMJ-Y',$csv);
  $this->assertStringNotContainsString('Verifikasi',$csv);$this->assertStringNotContainsString('Pemakaian',$csv);
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
}
