<?php
namespace Tests\Unit;
use App\Services\StoPdfParser;
use PHPUnit\Framework\TestCase;
class StoPdfParserTest extends TestCase {
 public function test_actual_uploaded_pdf_extracts_62_orders_and_correct_decimal_values(): void {
  $result=(new StoPdfParser())->parse(__DIR__.'/../Fixtures/STO.pdf');
  $this->assertCount(62,$result['rows']);$this->assertSame([],$result['warnings']);
  $first=$result['rows'][0];$this->assertSame('MUAT',$first['activity']);$this->assertSame('5520078169',$first['number']);$this->assertSame('2026-01-05',$first['document_date']);$this->assertSame('Urea SUB @50 KG',$first['items'][0]['name']);$this->assertSame('185.000',$first['items'][0]['quantity']);$this->assertSame('2617750.00',$first['total']);
  $count=array_count_values(array_column($result['rows'],'activity'));$this->assertSame(52,$count['MUAT']);$this->assertSame(10,$count['BONGKAR']);
  $this->assertSame('5520078285',$result['rows'][11]['number']);$this->assertSame('BONGKAR',$result['rows'][11]['activity']);
  $third=$result['rows'][2];$this->assertSame('172.400',$third['items'][0]['quantity']);$this->assertSame('14150.00',$third['items'][0]['rate']);$this->assertSame(3,$third['source_page']);
 }
 public function test_one_order_with_multiple_items(): void {
  $r=(new StoPdfParser())->parseText($this->text());
  $this->assertCount(1,$r['rows']);$this->assertCount(2,$r['rows'][0]['items']);$this->assertSame('MUAT',$r['rows'][0]['activity']);$this->assertSame('800.00',$r['rows'][0]['total']);
 }
 public function test_two_orders_on_same_page(): void {
  $r=(new StoPdfParser())->parseText($this->text().str_replace('1234567890','1234567891',$this->text()));$this->assertCount(2,$r['rows']);
 }
 public function test_scan_or_unknown_format_is_rejected(): void {
  $this->expectException(\RuntimeException::class);(new StoPdfParser())->parseText('');
 }
 public function test_invalid_date_is_rejected(): void {
  $this->expectException(\RuntimeException::class);(new StoPdfParser())->parseText(str_replace('01.02.2026','31.02.2026',$this->text()));
 }
 public function test_activity_is_detected_per_order_from_the_document_sentence(): void {
  $bongkar=str_replace(['1234567890','Mohon dilaksanakan pemuatan barang berikut :'],['1234567891','Mohon dibongkar barang berikut :'],$this->text());
  $r=(new StoPdfParser())->parseText($this->text()."\n".$bongkar);
  $this->assertSame(['MUAT','BONGKAR'],array_column($r['rows'],'activity'));
 }
 public function test_order_without_activity_sentence_is_skipped_with_warning(): void {
  $unknown=str_replace(['1234567890','Mohon dilaksanakan pemuatan barang berikut :'],['1234567891','Mohon diproses barang berikut :'],$this->text());
  $r=(new StoPdfParser())->parseText($this->text()."\n".$unknown);
  $this->assertCount(1,$r['rows']);$this->assertSame('1234567890',$r['rows'][0]['number']);
  $this->assertCount(1,$r['warnings']);$this->assertStringContainsString('1234567891',$r['warnings'][0]);$this->assertStringContainsString('tidak ditemukan',$r['warnings'][0]);
 }
 public function test_sentence_with_both_words_is_rejected_as_ambiguous(): void {
  $this->expectException(\RuntimeException::class);
  (new StoPdfParser())->parseText(str_replace('Mohon dilaksanakan pemuatan barang berikut :','Mohon dibongkar dan dimuat barang berikut :',$this->text()));
 }
 public function test_indonesian_numbers(): void {
  $p=new StoPdfParser();$this->assertSame('420.750',$p->decimal('420,750',3));$this->assertSame('6942375.00',$p->decimal('6.942.375',2));
 }
 private function text(): string {return "[[PAGE:1]]\nNo. Po : 1234567890\nTanggal : 01.02.2026\nMohon dilaksanakan pemuatan barang berikut :\nAsal Barang : Pengelola Barang Asal :\nGudang Gresik\nTujuan Barang : Pengelola Barang Tujuan :\nGudang Malang\nModa : Truck\nNo. Nama Barang Qty Satuan Tarif Biaya\n1 Urea SUB @50 KG 2 TON 100 200\n2 NPK 3 TON 200 600\nTotal 800";}
}
