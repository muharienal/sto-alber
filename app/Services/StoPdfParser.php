<?php
namespace App\Services;
use Smalot\PdfParser\Parser;
use RuntimeException;
class StoPdfParser {
 public function parse(string $path): array {
  $pdf=(new Parser())->parseFile($path);
  $pages=$pdf->getPages();
  if(count($pages)>200) throw new RuntimeException('PDF melebihi 200 halaman.');
  $text='';
  foreach($pages as $i=>$page) $text.="\n[[PAGE:".($i+1)."]]\n".$page->getText();
  return $this->parseText($text);
 }
 public function parseText(string $text): array {
  $text=str_replace(["\r\n","\r","\xc2\xa0"],["\n","\n"," "],$text);
  preg_match_all('/No\.?\s*(?:Po|PO|STO)\s*:\s*([0-9]{6,40})/iu',$text,$matches,PREG_OFFSET_CAPTURE);
  if(!$matches[0]) throw new RuntimeException('Nomor STO tidak ditemukan. Gunakan PDF teks (bukan scan) dengan format STO standar.');
  $rows=[];$warnings=[];
  foreach($matches[0] as $i=>$hit){
   $offset=$hit[1];$number=$matches[1][$i][0];
   $block=substr($text,$offset,($matches[0][$i+1][1]??strlen($text))-$offset);
   preg_match_all('/\[\[PAGE:(\d+)\]\]/',substr($text,0,$offset),$pm);
   $page=(int)(end($pm[1])?:1);
   try {
    $activity=$this->activity($block);
    if(!preg_match('/(?<!Cetak\s)\bTanggal\s*:\s*(\d{2})[.\/-](\d{2})[.\/-](\d{4})/i',$block,$d) || !checkdate((int)$d[2],(int)$d[1],(int)$d[3])) throw new RuntimeException('Tanggal dokumen tidak valid.');
    $origin=$this->location($block,'Asal','Tujuan\s+Barang');
    $destination=$this->location($block,'Tujuan','Moda');
    if(!preg_match('/No\.?\s+Nama\s+Barang\s+Qty\s+Satuan\s+Tarif\s+Biaya(.*?)\bTotal\s*:?\s*([\d.,]+)/is',$block,$table)) throw new RuntimeException('Tabel barang atau total tidak terbaca.');
    preg_match_all('/(?:^|\s)(\d+)\s+(.+?)\s+([\d.,]+)\s+([A-Z]{1,20})\s+([\d.,]+)\s+([\d.,]+)(?=\s|$)/s',trim($table[1]),$items,PREG_SET_ORDER);
    if(!$items) throw new RuntimeException('Baris barang tidak terbaca.');
    $detail=[];$sum=0;$notes=[];
    foreach($items as $item){
     $qty=$this->decimal($item[3],3);$rate=$this->decimal($item[5],2);$amount=$this->decimal($item[6],2);
     if((float)$qty<=0) throw new RuntimeException('Qty harus lebih besar dari nol.');
     if(abs(round((float)$qty*(float)$rate)-(float)$amount)>1) $notes[]='Baris '.$item[1].': Qty × Tarif tidak sama dengan Biaya.';
     $detail[]=['line'=>(int)$item[1],'name'=>preg_replace('/\s+/',' ',trim($item[2])),'quantity'=>$qty,'unit'=>$item[4],'rate'=>$rate,'amount'=>$amount];$sum+=(float)$amount;
    }
    if(count(array_unique(array_column($detail,'line')))!==count($detail)) throw new RuntimeException('Nomor baris barang ganda.');
    $total=$this->decimal($table[2],2);
    if(abs($sum-(float)$total)>1) $notes[]='Total tidak sama dengan jumlah Biaya.';
    $rows[]=['number'=>$number,'document_date'=>"{$d[3]}-{$d[2]}-{$d[1]}",'activity'=>$activity,'origin'=>$origin,'destination'=>$destination,'total'=>$total,'source_page'=>$page,'items'=>$detail,'notes'=>array_values(array_unique($notes))];
   }catch(RuntimeException $e){$warnings[]="STO $number (hal. $page): ".$e->getMessage();}
  }
  if(!$rows) throw new RuntimeException(implode(' ',array_slice($warnings,0,5)));
  return ['rows'=>$rows,'warnings'=>$warnings];
 }
 /**
  * Jenis kegiatan dibaca dari kalimat pembuka tiap STO, bukan dari pilihan pengguna:
  *   "Mohon dibongkar barang berikut :"            => BONGKAR
  *   "Mohon dilaksanakan pemuatan barang berikut :" => MUAT
  * Bila kalimat tidak ditemukan, bagian kepala dokumen (sebelum "Asal Barang") diperiksa.
  * Jika hasilnya tidak ada atau ambigu (memuat kedua kata), STO ditolak agar tidak salah simpan.
  */
 private function activity(string $block): string {
  $text=trim(preg_replace('/\s+/u',' ',preg_replace('/\[\[PAGE:\d+\]\]/','',$block)));
  if(preg_match('/\bMohon\b(.{0,120}?)\bberikut\b/iu',$text,$m)) $scope=$m[1];
  elseif(preg_match('/^(.*?)\bAsal\s+Barang\b/iu',$text,$h)) $scope=$h[1];
  else $scope='';
  $bongkar=(bool)preg_match('/\b\w*bongkar\w*\b/iu',$scope);
  $muat=(bool)preg_match('/\b\w*muat\w*\b/iu',$scope);
  if($bongkar===$muat) throw new RuntimeException($bongkar ? 'Kegiatan ambigu: memuat kata bongkar dan muat sekaligus.' : 'Kegiatan (bongkar/muat) tidak ditemukan.');
  return $bongkar ? 'BONGKAR' : 'MUAT';
 }
 private function location(string $block,string $label,string $next): string {
  if(!preg_match('/(?<!Pengelola\s)\b'.$label.'\s+Barang\s*:(.*?)\b'.$next.'\s*:/is',$block,$m)) throw new RuntimeException("$label barang tidak terbaca.");
  $value=preg_replace('/Pengelola\s+Barang\s+(?:Asal|Tujuan)\s*:[^\n]*/i','',$m[1]);
  $value=preg_replace('/\[\[PAGE:\d+\]\]/','',$value);
  $value=trim(preg_replace('/[ \t]+/',' ',$value));
  if($value==='') throw new RuntimeException("$label barang kosong.");
  return $value;
 }
 public function decimal(string $value,int $scale): string {
  // Format Indonesia: titik ribuan, koma desimal. Tidak mengubah 172,400 menjadi 172400.
  if(!preg_match('/^(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d{1,'.$scale.'})?$/',$value)) throw new RuntimeException('Format angka tidak valid: '.$value);
  $normalized=str_replace(',','.',str_replace('.','',$value));
  [$whole,$fraction]=array_pad(explode('.',$normalized,2),2,'');
  if(strlen(ltrim($whole,'0'))>18-$scale) throw new RuntimeException('Angka melebihi kapasitas kolom.');
  return $whole.'.'.str_pad($fraction,$scale,'0');
 }
}
