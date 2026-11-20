<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sto extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['document_date' => 'date', 'total' => 'decimal:2', 'deleted_at' => 'datetime'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StoItem::class)->orderBy('line');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /** Riwayat, terbaru di atas. */
    public function activities(): HasMany
    {
        return $this->hasMany(StoActivity::class)->latest('created_at')->orderByDesc('id');
    }

    /** Nama kolom referensi (SOJ/MMJ) yang masih kosong, untuk peringatan di tampilan. */
    public function missingReferences(): array
    {
        return array_keys(array_filter(
            ['SOJ' => $this->soj, 'MMJ' => $this->mmj],
            fn ($value) => blank($value)
        ));
    }

    /** STO yang SOJ atau MMJ-nya masih kosong. */
    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->where(fn (Builder $w) => $w
            ->whereNull('soj')->orWhere('soj', '')
            ->orWhereNull('mmj')->orWhere('mmj', ''));
    }

    /** STO yang SOJ dan MMJ-nya sudah terisi. */
    public function scopeComplete(Builder $query): Builder
    {
        return $query
            ->whereNotNull('soj')->where('soj', '!=', '')
            ->whereNotNull('mmj')->where('mmj', '!=', '');
    }

    /**
     * Potret isi STO untuk dibandingkan sebelum/sesudah diubah (riwayat).
     * Barang diringkas menjadi "n baris" agar riwayat tetap pendek.
     */
    public function snapshot(): array
    {
        $this->loadMissing('items');

        return [
            'number' => $this->number,
            'document_date' => $this->document_date?->format('Y-m-d'),
            'activity' => $this->activity,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'soj' => blank($this->soj) ? null : $this->soj,
            'mmj' => blank($this->mmj) ? null : $this->mmj,
            'total' => number_format((float) $this->total, 2, '.', ''),
            'items' => $this->items->map(fn ($i) => implode('|', [$i->name, (float) $i->quantity, $i->unit, (float) $i->rate]))->implode(';'),
            '_item_count' => $this->items->count(),
        ];
    }

    /** Selisih dua potret: ['soj' => [lama, baru], ...]. */
    public static function diff(array $before, array $after): array
    {
        $changes = [];
        foreach (array_keys(StoActivity::FIELDS) as $field) {
            $old = $before[$field] ?? null;
            $new = $after[$field] ?? null;
            if ($old === $new) {
                continue;
            }
            $changes[$field] = $field === 'items'
                ? [$before['_item_count'].' baris', $after['_item_count'].' baris (rincian diubah)']
                : [$old, $new];
        }

        return $changes;
    }
}
