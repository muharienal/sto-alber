<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Rentang tanggal STO dari query string (?dari=YYYY-MM-DD&sampai=YYYY-MM-DD),
 * dipakai bersama oleh Dashboard dan Rekap. Kosong berarti "semua periode".
 */
final class Period
{
    public function __construct(public readonly ?string $from = null, public readonly ?string $to = null) {}

    public static function fromRequest(Request $request): self
    {
        $from = self::validDate($request->query('dari'));
        $to = self::validDate($request->query('sampai'));
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return new self($from, $to);
    }

    public static function validDate(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        [$y, $m, $d] = array_map('intval', explode('-', $value));

        return checkdate($m, $d, $y) ? $value : null;
    }

    /** Pilihan cepat: kunci => [label, dari, sampai]. */
    public static function presets(): array
    {
        $now = now();
        $last = $now->copy()->subMonthNoOverflow();

        return [
            'bulan-ini' => ['Bulan ini', $now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'bulan-lalu' => ['Bulan lalu', $last->copy()->startOfMonth()->toDateString(), $last->copy()->endOfMonth()->toDateString()],
            'tahun-ini' => ['Tahun ini', $now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            'semua' => ['Semua', null, null],
        ];
    }

    /** Kunci preset yang cocok dengan rentang ini, atau null bila rentang bebas. */
    public function presetKey(): ?string
    {
        foreach (self::presets() as $key => [, $from, $to]) {
            if ($from === $this->from && $to === $this->to) {
                return $key;
            }
        }

        return null;
    }

    public function isAll(): bool
    {
        return $this->from === null && $this->to === null;
    }

    /** Parameter untuk tautan (route(..., $period->query())). */
    public function query(): array
    {
        return array_filter(['dari' => $this->from, 'sampai' => $this->to]);
    }

    /** Teks periode untuk judul, mis. "1 November 2026 – 30 November 2026". */
    public function label(): string
    {
        if ($key = $this->presetKey()) {
            $label = self::presets()[$key][0];
            if ($key === 'semua') {
                return 'Semua periode';
            }

            return $label.' ('.$this->rangeText().')';
        }

        return $this->rangeText();
    }

    private function rangeText(): string
    {
        $fmt = fn (string $d) => Carbon::parse($d)->translatedFormat('j M Y');

        return match (true) {
            $this->from && $this->to => $fmt($this->from).' – '.$fmt($this->to),
            (bool) $this->from => 'Sejak '.$fmt($this->from),
            (bool) $this->to => 'Sampai '.$fmt($this->to),
            default => 'Semua periode',
        };
    }

    /**
     * Batas bawah (inklusif) dan atas (eksklusif, hari berikutnya) untuk dibandingkan
     * dengan kolom tanggal. Batas atas eksklusif agar tetap benar bila kolom tersimpan
     * sebagai "YYYY-MM-DD 00:00:00".
     */
    public function bounds(): array
    {
        return [
            $this->from ?? '0001-01-01',
            $this->to ? Carbon::parse($this->to)->addDay()->toDateString() : '9999-12-31',
        ];
    }

    /** Terapkan rentang ke query pada kolom tanggal tertentu. */
    public function apply($query, string $column = 'document_date')
    {
        [$from, $until] = $this->bounds();

        return $query->where($column, '>=', $from)->where($column, '<', $until);
    }

    /** Jumlah hari dalam rentang, atau null bila salah satu batas kosong. */
    public function days(): ?int
    {
        return $this->from && $this->to ? (int) Carbon::parse($this->from)->diffInDays(Carbon::parse($this->to)) + 1 : null;
    }
}
