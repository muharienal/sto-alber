<?php

namespace App\Http\Requests;

use App\Models\Sto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi tambah dan ubah STO secara manual.
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
                Rule::unique('stos', 'number')->ignore($current instanceof Sto ? $current->id : null),
            ],
            'document_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31'],
            'activity' => ['required', 'in:BONGKAR,MUAT'],
            'origin' => ['required', 'string', 'max:1000'],
            'destination' => ['required', 'string', 'max:1000'],
            'soj' => ['nullable', 'string', 'max:100'],
            'mmj' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:1000000'],
            'items.*.unit' => ['required', 'string', 'max:20'],
            'items.*.rate' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:10000000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'number' => 'Nomor STO',
            'document_date' => 'Tanggal',
            'activity' => 'Kegiatan',
            'origin' => 'Asal',
            'destination' => 'Tujuan',
            'soj' => 'SOJ',
            'mmj' => 'MMJ',
            'items' => 'Barang',
            'items.*.name' => 'Nama barang',
            'items.*.quantity' => 'Qty',
            'items.*.unit' => 'Satuan',
            'items.*.rate' => 'Tarif',
        ];
    }

    public function messages(): array
    {
        return self::indonesianMessages();
    }

    /** Pesan validasi berbahasa Indonesia (aplikasi belum memiliki berkas lang). */
    public static function indonesianMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'numeric' => ':attribute harus berupa angka.',
            'decimal' => ':attribute maksimal :decimal angka di belakang koma.',
            'in' => ':attribute tidak valid.',
            'regex' => ':attribute hanya boleh berisi huruf, angka, titik, garis miring, garis bawah, dan strip.',
            'unique' => ':attribute sudah dipakai STO lain.',
            'date_format' => ':attribute tidak valid.',
            'after_or_equal' => ':attribute tidak boleh sebelum :date.',
            'before_or_equal' => ':attribute tidak boleh setelah :date.',
            'array' => ':attribute tidak valid.',
            'max' => [
                'string' => ':attribute maksimal :max karakter.',
                'numeric' => ':attribute maksimal :max.',
                'array' => 'Maksimal :max baris barang.',
            ],
            'min' => [
                'numeric' => ':attribute minimal :min.',
                'array' => 'Isi minimal :min baris barang.',
            ],
            'gt' => [
                'numeric' => ':attribute harus lebih besar dari :value.',
            ],
        ];
    }
}
