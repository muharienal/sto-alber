<?php

namespace App\Http\Requests;

use App\Models\Sto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi tambah dan ubah STO secara manual (pesan: lang/id/validation.php).
 * Kesalahan disimpan pada error bag "sto" agar tidak tercampur dengan
 * kesalahan unggah PDF pada halaman yang sama.
 */
class StoRequest extends FormRequest
{
    protected $errorBag = 'sto';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items');
        $this->merge([
            'items' => is_array($items) ? array_values(array_filter($items, 'is_array')) : [],
        ]);
    }

    public function rules(): array
    {
        $current = $this->route('sto');

        return [
            'number' => [
                'required', 'string', 'max:40',
                'regex:/^[0-9A-Za-z][0-9A-Za-z._\/-]*$/',
                Rule::unique('stos', 'number')->ignore($current instanceof Sto ? $current->id : null)->whereNull('deleted_at'),
                function (string $attribute, mixed $value, \Closure $fail) use ($current) {
                    $inTrash = Sto::onlyTrashed()->where('number', trim((string) $value))
                        ->when($current instanceof Sto, fn ($q) => $q->whereKeyNot($current->id))->exists();
                    if ($inTrash) {
                        $fail('Nomor STO ini ada di Sampah. Pulihkan dari menu Sampah, atau hapus permanen dulu.');
                    }
                },
            ],
            'document_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31'],
            'activity' => ['required', 'in:BONGKAR,MUAT'],
            'origin' => ['required', 'string', 'max:1000'],
            'destination' => ['required', 'string', 'max:1000'],
            'soj' => ['nullable', 'string', 'max:100'],
            'mmj' => ['nullable', 'string', 'max:100'],
            // Hanya saat menambah manual dari STO gagal-baca: tautkan ke PDF impor milik pengguna sendiri.
            'import_batch_id' => ['nullable', 'integer', Rule::exists('import_batches', 'id')->where('user_id', $this->user()?->id)],
            'source_page' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:1000000'],
            'items.*.unit' => ['required', 'string', 'max:20'],
            'items.*.rate' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:10000000'],
        ];
    }

    // Pesan dan nama kolom ada di lang/id/validation.php.
}
