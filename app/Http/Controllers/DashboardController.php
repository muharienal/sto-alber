<?php

namespace App\Http\Controllers;

use App\Models\Sto;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $total = Sto::count();
        $bongkar = Sto::where('activity', 'BONGKAR')->count();
        $incomplete = Sto::incomplete()->count();
        $sojEmpty = Sto::where(fn ($w) => $w->whereNull('soj')->orWhere('soj', ''))->count();
        $mmjEmpty = Sto::where(fn ($w) => $w->whereNull('mmj')->orWhere('mmj', ''))->count();

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $days[] = ['label' => $day->format('d M'), 'count' => Sto::whereDate('created_at', $day->toDateString())->count()];
        }

        return view('dashboard', compact('total', 'bongkar', 'incomplete', 'sojEmpty', 'mmjEmpty', 'days') + [
            'amount' => Sto::sum('total'),
            'latest' => Sto::latest()->orderByDesc('id')->limit(5)->get(),
        ]);
    }
}
