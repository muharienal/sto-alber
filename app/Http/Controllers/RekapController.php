<?php

namespace App\Http\Controllers;

use App\Models\Sto;
use App\Support\Period;
use App\Support\Tonnage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** Rekap per periode: total tonase dan nilai per bulan, per barang, per kegiatan, dan per rute. */
class RekapController extends Controller
{
    public function __invoke(Request $request)
    {
        $period = Period::fromRequest($request);

        // 1 query: satu baris per STO dengan tonase-nya; dikelompokkan di PHP (per bulan, kegiatan, rute).
        $stos = $period->apply(Sto::toBase(), 'stos.document_date')
            ->select('stos.id', 'stos.document_date', 'stos.activity', 'stos.origin', 'stos.destination', 'stos.total')
            ->selectRaw('(SELECT COALESCE(SUM('.Tonnage::expr().'), 0) FROM sto_items WHERE sto_items.sto_id = stos.id) AS ton')
            ->orderBy('stos.document_date')
            ->get();

        // 1 query: per barang (nama + satuan), langsung dikelompokkan di database.
        $items = $period->apply(
            DB::table('sto_items')->join('stos', 'stos.id', '=', 'sto_items.sto_id')->whereNull('stos.deleted_at'),
            'stos.document_date'
        )
            ->groupBy('sto_items.name', 'sto_items.unit')
            ->selectRaw('sto_items.name, sto_items.unit, SUM(sto_items.quantity) AS qty, SUM('.Tonnage::expr().') AS ton, SUM(sto_items.amount) AS amount, COUNT(DISTINCT stos.id) AS stos')
            ->orderByDesc('amount')
            ->get();

        $byMonth = $this->group($stos, fn ($s) => substr((string) $s->document_date, 0, 7));
        foreach ($byMonth as $key => &$row) {
            $row['label'] = Carbon::createFromFormat('Y-m-d', $key.'-01')->translatedFormat('F Y');
        }
        unset($row);

        $byActivity = $this->group($stos, fn ($s) => $s->activity);
        ksort($byActivity);

        $byRoute = $this->group($stos, fn ($s) => $this->place($s->origin).' → '.$this->place($s->destination));
        uasort($byRoute, fn ($a, $b) => [$b['ton'], $b['amount']] <=> [$a['ton'], $a['amount']]);

        return view('rekap', [
            'period' => $period,
            'summary' => ['stos' => $stos->count(), 'ton' => (float) $stos->sum('ton'), 'amount' => (float) $stos->sum('total')],
            'byMonth' => $byMonth,
            'byActivity' => $byActivity,
            'byRoute' => $byRoute,
            'byItem' => $items,
        ]);
    }

    /** Jumlahkan STO, tonase, dan nilai per kunci. */
    private function group($stos, callable $keyOf): array
    {
        $out = [];
        foreach ($stos as $s) {
            $key = $keyOf($s);
            $out[$key] ??= ['label' => $key, 'stos' => 0, 'ton' => 0.0, 'amount' => 0.0];
            $out[$key]['stos']++;
            $out[$key]['ton'] += (float) $s->ton;
            $out[$key]['amount'] += (float) $s->total;
        }

        return $out;
    }

    /** Nama tempat untuk rute: baris pertama alamat (nama gudang/pelabuhan), tanpa jalan. */
    private function place(?string $address): string
    {
        $first = trim(strtok(str_replace("\r", '', (string) $address), "\n") ?: '');

        return preg_replace('/\s+/', ' ', $first) ?: '—';
    }
}
