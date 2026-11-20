<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Penulis file Excel (.xlsx) sederhana tanpa library tambahan.
 *
 * Angka disimpan sebagai angka asli (bukan teks), sehingga Excel dengan pengaturan
 * Indonesia maupun Inggris membacanya sama persis: 19,5 ton tetap 19,5 ton.
 * Baris ditulis bertahap ke file sementara agar hemat memori untuk data besar.
 *
 * Jenis sel: 'text', 'date' (Y-m-d), 'qty' (3 desimal), 'money' (tanpa desimal), 'int'.
 */
class XlsxWriter
{
    private const STYLE = ['text' => 0, 'header' => 1, 'date' => 2, 'qty' => 3, 'money' => 4, 'int' => 5];

    private $sheet;

    private string $sheetPath;

    private int $row = 0;

    /** @param array<int, array{0:string, 1:string, 2:float}> $columns [judul, jenis, lebar] */
    public function __construct(private array $columns)
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP zip tidak aktif.');
        }
        $this->sheetPath = tempnam(sys_get_temp_dir(), 'xlsx-sheet-');
        $this->sheet = fopen($this->sheetPath, 'w');

        $cols = '';
        foreach ($columns as $i => $c) {
            $n = $i + 1;
            $cols .= '<col min="'.$n.'" max="'.$n.'" width="'.$c[2].'" customWidth="1"/>';
        }
        fwrite($this->sheet, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<cols>'.$cols.'</cols><sheetData>');

        $this->writeRow(array_column($columns, 0), true);
    }

    /** @param array<int, mixed> $values urutan sesuai kolom; null = sel kosong */
    public function addRow(array $values): void
    {
        $this->writeRow($values, false);
    }

    private function writeRow(array $values, bool $header): void
    {
        $this->row++;
        $xml = '<row r="'.$this->row.'">';
        foreach ($this->columns as $i => $col) {
            $value = $values[$i] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            $ref = self::colName($i).$this->row;
            $type = $header ? 'header' : $col[1];
            $style = self::STYLE[$type];

            if ($type === 'date') {
                $serial = (int) ((strtotime($value.' 00:00:00 UTC') / 86400) + 25569); // hari sejak 30-12-1899
                $xml .= '<c r="'.$ref.'" s="'.$style.'"><v>'.$serial.'</v></c>';
            } elseif (in_array($type, ['qty', 'money', 'int'], true)) {
                $xml .= '<c r="'.$ref.'" s="'.$style.'"><v>'.self::number($value).'</v></c>';
            } else {
                $xml .= '<c r="'.$ref.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'.self::text((string) $value).'</t></is></c>';
            }
        }
        fwrite($this->sheet, $xml.'</row>');
    }

    /** Simpan ke file .xlsx dan kembalikan path-nya. */
    public function save(string $sheetName = 'Data'): string
    {
        $last = self::colName(count($this->columns) - 1).max(1, $this->row);
        fwrite($this->sheet, '</sheetData><autoFilter ref="A1:'.$last.'"/></worksheet>');
        fclose($this->sheet);

        $path = tempnam(sys_get_temp_dir(), 'xlsx-').'.xlsx';
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('File Excel gagal dibuat.');
        }
        $name = self::text(mb_substr($sheetName, 0, 31));
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.$name.'" sheetId="1" r:id="rId1"/></sheets><definedNames><definedName name="_xlnm._FilterDatabase" localSheetId="0" hidden="1">\''.$name.'\'!$A$1:$'.preg_replace('/\d+/', '', $last).'$'.max(1, $this->row).'</definedName></definedNames></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="3"><numFmt numFmtId="164" formatCode="dd/mm/yyyy"/><numFmt numFmtId="165" formatCode="#,##0.000"/><numFmt numFmtId="166" formatCode="#,##0"/></numFmts>'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFACC15"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="6">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top"/></xf>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment vertical="top"/></xf>'
            .'<xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment vertical="top"/></xf>'
            .'<xf numFmtId="166" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment vertical="top"/></xf>'
            .'<xf numFmtId="1" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment vertical="top"/></xf>'
            .'</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>');
        $zip->addFile($this->sheetPath, 'xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($this->sheetPath);

        return $path;
    }

    private static function colName(int $index): string
    {
        $name = '';
        for ($n = $index + 1; $n > 0; $n = intdiv($n - 1, 26)) {
            $name = chr(65 + ($n - 1) % 26).$name;
        }

        return $name;
    }

    /** Angka dari string desimal database ("19.500") ke bentuk XML yang selalu memakai titik. */
    private static function number(mixed $value): string
    {
        $s = is_string($value) ? trim($value) : (string) $value;
        if (! preg_match('/^-?\d+(\.\d+)?$/', $s)) {
            return '0';
        }
        if (str_contains($s, '.')) {
            $s = rtrim(rtrim($s, '0'), '.'); // "19.500" -> "19.5", "100.00" -> "100"
        }

        return $s === '' || $s === '-' ? '0' : $s;
    }

    private static function text(string $value): string
    {
        $value = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $value) ?? '';

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
