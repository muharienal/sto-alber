<?php

namespace App\Support;

/**
 * Ekspresi SQL untuk menghitung tonase dari baris barang (sto_items).
 * TON dihitung apa adanya, KG dibagi 1.000. Satuan lain (mis. UNIT, ZAK) tidak bisa
 * diubah ke ton, jadi tidak dihitung dan jumlah barisnya dilaporkan terpisah.
 */
final class Tonnage
{
    private const TON_UNITS = "'TON','TONS','TONNE','MT'";

    private const KG_UNITS = "'KG','KGS'";

    public static function expr(string $table = 'sto_items'): string
    {
        $unit = "UPPER(TRIM($table.unit))";

        return "(CASE WHEN $unit IN (".self::TON_UNITS.") THEN $table.quantity"
            ." WHEN $unit IN (".self::KG_UNITS.") THEN $table.quantity / 1000.0 ELSE 0 END)";
    }

    /** 1 untuk baris yang satuannya tidak bisa diubah ke ton, selain itu 0. */
    public static function otherExpr(string $table = 'sto_items'): string
    {
        $unit = "UPPER(TRIM($table.unit))";

        return "(CASE WHEN $unit IN (".self::TON_UNITS.','.self::KG_UNITS.') THEN 0 ELSE 1 END)';
    }
}
