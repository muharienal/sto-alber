<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu baris riwayat STO: siapa melakukan apa, kapan, dan nilai lama → baru. */
class StoActivity extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['changes' => 'array', 'created_at' => 'datetime'];
    }

    /** Label kolom untuk ditampilkan di riwayat. */
    public const FIELDS = [
        'number' => 'Nomor STO',
        'document_date' => 'Tanggal',
        'activity' => 'Kegiatan',
        'origin' => 'Asal',
        'destination' => 'Tujuan',
        'soj' => 'SOJ',
        'mmj' => 'MMJ',
        'total' => 'Total',
        'items' => 'Barang',
    ];

    public const ACTIONS = [
        'imported' => 'Diimpor dari PDF',
        'created' => 'Ditambahkan manual',
        'updated' => 'Data diubah',
        'references' => 'SOJ/MMJ diisi',
        'deleted' => 'Dipindah ke Sampah',
        'restored' => 'Dipulihkan dari Sampah',
        'purged' => 'Dihapus permanen',
    ];

    public function sto(): BelongsTo
    {
        return $this->belongsTo(Sto::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    /** Catat kejadian untuk satu STO. Perubahan kosong pada aksi "updated"/"references" tidak dicatat. */
    public static function record(Sto $sto, string $action, array $changes = [], ?string $note = null): ?self
    {
        if (in_array($action, ['updated', 'references'], true) && $changes === []) {
            return null;
        }
        $user = auth()->user();

        return self::create([
            'sto_id' => $sto->id,
            'sto_number' => $sto->number,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => $action,
            'changes' => $changes ?: null,
            'note' => $note,
        ]);
    }
}
