<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sto extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['document_date' => 'date', 'total' => 'decimal:2'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StoItem::class)->orderBy('line');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
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
}
