<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ImportBatch extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'warnings' => 'array', 'failed' => 'array', 'expires_at' => 'datetime'];
    }

    public function stos(): HasMany
    {
        return $this->hasMany(Sto::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * STO gagal-baca yang masih perlu ditambahkan manual untuk pengguna ini:
     * dari impor yang sudah disimpan, nomornya belum ada di Master STO maupun Sampah.
     *
     * @return Collection<int, array{batch: self, row: array}>
     */
    public static function pendingFailedFor(User $user): Collection
    {
        $batches = self::where('user_id', $user->id)->where('status', 'committed')
            ->whereNotNull('failed')->latest()->limit(20)->get()
            ->filter(fn (self $b) => ! empty($b->failed));
        if ($batches->isEmpty()) {
            return collect();
        }

        $numbers = $batches->flatMap(fn (self $b) => array_column($b->failed, 'number'))->unique()->values()->all();
        $existing = array_flip(Sto::withTrashed()->whereIn('number', $numbers)->pluck('number')->all());

        return $batches->flatMap(fn (self $b) => collect($b->failed)
            ->reject(fn (array $row) => isset($existing[$row['number']]))
            ->map(fn (array $row) => ['batch' => $b, 'row' => $row]))
            ->values();
    }
}
