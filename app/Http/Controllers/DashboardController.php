<?php

namespace App\Http\Controllers;

use App\Models\Sto;
use App\Support\Period;
use App\Support\Tonnage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** Grafik per hari bila periode paling lama sekian hari, selain itu per bulan. */
    private const DAILY_MAX_DAYS = 62;

    /** Batas jumlah bulan di grafik untuk "Semua periode". */
    private const MAX_MONTHS = 24;

    public function __invoke(Request $request)
    {
        $period = Period::fromRequest($request);
        [$from, $until] = $period->bounds();
        $inPeriod = 'document_date >= ? AND document_date < ?';
        $blank = fn (string $col) => "($col IS NULL OR $col = '')";

        // 1 query: angka periode + kelengkapan (kelengkapan selalu seluruh STO, karena itu daftar pekerjaan).
        $s = Sto::toBase()->selectRaw(
            "SUM(CASE WHEN $inPeriod THEN 1 ELSE 0 END) AS total,
             SUM(CASE WHEN $inPeriod THEN total ELSE 0 END) AS amount,
             SUM(CASE WHEN $inPeriod AND activity = 'BONGKAR' THEN 1 ELSE 0 END) AS bongkar,
             COUNT(*) AS all_total,
             SUM(CASE WHEN {$blank('soj')} THEN 1 ELSE 0 END) AS soj_empty,
             SUM(CASE WHEN {$blank('mmj')} THEN 1 ELSE 0 END) AS mmj_empty,
             SUM(CASE WHEN {$blank('soj')} OR {$blank('mmj')} THEN 1 ELSE 0 END) AS incomplete",
            [$from, $until, $from, $until, $from, $until]
        )->first();

        // 1 query: jumlah STO dan tonase per tanggal dalam periode, untuk total tonase dan grafik.
        $perDate = $period->apply(
            DB::table('stos')
                ->leftJoin('sto_items', 'sto_items.sto_id', '=', 'stos.id')
                ->whereNull('stos.deleted_at'),
            'stos.document_date'
        )
            ->groupBy('stos.document_date')
            ->selectRaw('stos.document_date AS d, COUNT(DISTINCT stos.id) AS stos, SUM('.Tonnage::expr().') AS ton, SUM(CASE WHEN sto_items.id IS NULL THEN 0 ELSE '.Tonnage::otherExpr().' END) AS other')
            ->get();

        $total = (int) $s->total;

        return view('dashboard', [
            'period' => $period,
            'total' => $total,
            'amount' => (float) $s->amount,
            'bongkar' => (int) $s->bongkar,
            'allTotal' => (int) $s->all_total,
            'incomplete' => (int) $s->incomplete,
            'sojEmpty' => (int) $s->soj_empty,
            'mmjEmpty' => (int) $s->mmj_empty,
            'tonnage' => (float) $perDate->sum('ton'),
            'otherUnitRows' => (int) $perDate->sum('other'),
            'chart' => $this->chart($period, $perDate),
            'latest' => Sto::latest()->orderByDesc('id')->limit(5)->get(),
        ]);
    }

    /**
     * Susun batang grafik tonase: per hari untuk periode pendek, per bulan untuk periode panjang.
     * Pengelompokan dilakukan di PHP agar sama di MySQL dan SQLite.
     */
    private function chart(Period $period, $perDate): array
    {
        $days = $period->days();
        $daily = $days !== null && $days <= self::DAILY_MAX_DAYS;
        $keyOf = fn (string $date) => $daily ? substr($date, 0, 10) : substr($date, 0, 7);

        $sums = [];
        foreach ($perDate as $row) {
            $key = $keyOf((string) $row->d);
            $sums[$key]['ton'] = ($sums[$key]['ton'] ?? 0) + (float) $row->ton;
            $sums[$key]['stos'] = ($sums[$key]['stos'] ?? 0) + (int) $row->stos;
        }

        // Rentang sumbu: periode yang dipilih, atau dari data yang ada bila periodenya terbuka.
        $known = array_keys($sums);
        sort($known);
        $start = $period->from ?? ($known ? $known[0].($daily ? '' : '-01') : null);
        $end = $period->to ?? ($known ? end($known).($daily ? '' : '-01') : null);
        if ($start === null || $end === null) {
            return ['daily' => $daily, 'bars' => []];
        }

        $cursor = Carbon::parse($start)->startOfDay();
        $last = Carbon::parse($end)->startOfDay();
        if (! $daily) {
            $cursor->startOfMonth();
            $last->startOfMonth();
            if ($cursor->diffInMonths($last) >= self::MAX_MONTHS) {
                $cursor = $last->copy()->subMonthsNoOverflow(self::MAX_MONTHS - 1);
            }
        }

        $bars = [];
        while ($cursor <= $last) {
            $key = $daily ? $cursor->toDateString() : $cursor->format('Y-m');
            $bars[] = [
                'label' => $daily ? $cursor->format('j') : $cursor->translatedFormat('M y'),
                'title' => $daily ? $cursor->translatedFormat('j F Y') : $cursor->translatedFormat('F Y'),
                'ton' => $sums[$key]['ton'] ?? 0.0,
                'stos' => $sums[$key]['stos'] ?? 0,
            ];
            $daily ? $cursor->addDay() : $cursor->addMonthNoOverflow();
        }

        return ['daily' => $daily, 'bars' => $bars];
    }
}
