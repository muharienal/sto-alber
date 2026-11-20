<?php

namespace App\Support;

/** Format angka gaya Indonesia, dipakai di semua tampilan agar konsisten. */
class Format
{
    /** Rupiah tanpa desimal: 2672450 -> "2.672.450". */
    public static function rupiah(mixed $value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }

    /**
     * Qty tanpa nol yang tidak perlu: 19.000 -> "19", 19.500 -> "19,5", 1250.125 -> "1.250,125".
     * Menghindari "19,000" yang bisa terbaca sembilan belas ribu.
     */
    public static function qty(mixed $value, int $maxDecimals = 3): string
    {
        $s = number_format((float) $value, $maxDecimals, ',', '.');
        if (str_contains($s, ',')) {
            $s = rtrim(rtrim($s, '0'), ',');
        }

        return $s;
    }

    /** Tonase ringkas untuk kartu angka: 1250.5 -> "1.250,5". */
    public static function ton(mixed $value): string
    {
        return self::qty($value, (float) $value >= 100 ? 1 : 3);
    }
}
