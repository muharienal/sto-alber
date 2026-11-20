<?php

/*
 * Pesan validasi berbahasa Indonesia untuk seluruh aplikasi (locale "id").
 * Satu tempat untuk semua pesan; form tidak perlu menulis ulang pesan sendiri.
 */
return [
    'accepted' => ':attribute harus disetujui.',
    'after_or_equal' => ':attribute tidak boleh sebelum :date.',
    'array' => ':attribute tidak valid.',
    'before_or_equal' => ':attribute tidak boleh setelah :date.',
    'boolean' => ':attribute harus ya atau tidak.',
    'confirmed' => 'Ulangan :attribute tidak sama.',
    'current_password' => 'Password saat ini salah.',
    'date' => ':attribute bukan tanggal yang valid.',
    'date_format' => ':attribute tidak valid.',
    'decimal' => ':attribute maksimal :decimal angka di belakang koma.',
    'different' => ':attribute harus berbeda dari :other.',
    'email' => ':attribute harus alamat email yang valid.',
    'exists' => ':attribute tidak ditemukan.',
    'extensions' => ':attribute harus berformat :values.',
    'file' => ':attribute harus berupa file.',
    'gt' => [
        'numeric' => ':attribute harus lebih besar dari :value.',
    ],
    'in' => ':attribute tidak valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'max' => [
        'array' => 'Maksimal :max baris :attribute.',
        'file' => 'Ukuran :attribute maks. :max KB.',
        'numeric' => ':attribute maksimal :max.',
        'string' => ':attribute maksimal :max karakter.',
    ],
    'mimes' => ':attribute harus berformat :values.',
    'min' => [
        'array' => 'Isi minimal :min baris :attribute.',
        'numeric' => ':attribute minimal :min.',
        'string' => ':attribute minimal :min karakter.',
    ],
    'numeric' => ':attribute harus berupa angka.',
    'regex' => ':attribute hanya boleh berisi huruf, angka, titik, garis miring, garis bawah, dan strip.',
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah dipakai.',
    'uploaded' => ':attribute gagal diunggah.',

    'password' => [
        'letters' => ':attribute harus memuat huruf.',
        'mixed' => ':attribute harus memuat huruf besar dan kecil.',
        'numbers' => ':attribute harus memuat angka.',
        'symbols' => ':attribute harus memuat simbol.',
        'uncompromised' => ':attribute ini pernah bocor di internet. Pilih yang lain.',
    ],

    /* Pesan khusus untuk kolom tertentu. */
    'custom' => [
        'pdf' => [
            'required' => 'Pilih file PDF terlebih dahulu.',
            'file' => 'File gagal diunggah. Coba lagi.',
            'uploaded' => 'File gagal diunggah. Pastikan ukurannya maks. 10 MB.',
            'mimes' => 'File harus berformat PDF.',
            'extensions' => 'File harus berformat PDF.',
            'max' => 'Ukuran PDF maks. 10 MB.',
        ],
        'number' => [
            'unique' => 'Nomor STO sudah dipakai STO lain.',
        ],
        'username' => [
            'unique' => 'Username sudah dipakai pengguna lain.',
            'regex' => 'Username hanya boleh huruf kecil, angka, titik, garis bawah, dan strip.',
        ],
    ],

    /* Nama kolom yang tampil di pesan. */
    'attributes' => [
        'username' => 'Username',
        'password' => 'Password',
        'current_password' => 'Password saat ini',
        'name' => 'Nama',
        'role' => 'Peran',
        'pdf' => 'PDF',
        'number' => 'Nomor STO',
        'document_date' => 'Tanggal',
        'activity' => 'Kegiatan',
        'origin' => 'Asal',
        'destination' => 'Tujuan',
        'soj' => 'SOJ',
        'mmj' => 'MMJ',
        'items' => 'barang',
        'items.*.name' => 'Nama barang',
        'items.*.quantity' => 'Qty',
        'items.*.unit' => 'Satuan',
        'items.*.rate' => 'Tarif',
        'import_batch_id' => 'PDF sumber',
        'source_page' => 'Halaman PDF',
        'refs' => 'STO',
        'refs.*.soj' => 'SOJ',
        'refs.*.mmj' => 'MMJ',
    ],
];
