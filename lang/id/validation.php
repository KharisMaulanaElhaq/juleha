<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut berisi pesan kesalahan default yang digunakan oleh
    | kelas validator. Beberapa aturan memiliki beberapa versi seperti aturan ukuran.
    | Silakan sesuaikan setiap pesan ini di sini.
    |
    */

    'accepted' => ':attribute harus diterima.',
    'accepted_if' => ':attribute harus diterima ketika :other bernilai :value.',
    'active_url' => ':attribute bukan URL yang valid.',
    'after' => ':attribute harus tanggal setelah :date.',
    'after_or_equal' => ':attribute harus tanggal setelah atau sama dengan :date.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, setrip, dan garis bawah.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'array' => ':attribute harus berupa array.',
    'ascii' => ':attribute hanya boleh berisi karakter alfanumerik satu bita dan simbol.',
    'before' => ':attribute harus tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':attribute harus memiliki antara :min dan :max anggota.',
        'file' => 'Ukuran berkas :attribute harus antara :min dan :max kilobita.',
        'numeric' => ':attribute harus bernilai antara :min dan :max.',
        'string' => ':attribute harus berisi antara :min dan :max karakter.',
    ],
    'boolean' => 'Bidang :attribute harus bernilai benar atau salah.',
    'can' => 'Bidang :attribute berisi nilai yang tidak diizinkan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'contains' => 'Bidang :attribute tidak memiliki nilai yang diperlukan.',
    'current_password' => 'Kata sandi salah.',
    'date' => ':attribute bukan tanggal yang valid.',
    'date_equals' => ':attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => ':attribute tidak cocok dengan format :format.',
    'decimal' => ':attribute harus memiliki :decimal tempat desimal.',
    'declined' => ':attribute harus ditolak.',
    'declined_if' => ':attribute harus ditolak ketika :other bernilai :value.',
    'different' => ':attribute dan :other harus berbeda.',
    'digits' => ':attribute harus berisi :digits digit.',
    'digits_between' => ':attribute harus berisi antara :min dan :max digit.',
    'dimensions' => 'Dimensi gambar :attribute tidak valid.',
    'distinct' => 'Bidang :attribute memiliki nilai duplikat.',
    'doesnt_end_with' => ':attribute tidak boleh diakhiri dengan salah satu dari berikut: :values.',
    'doesnt_start_with' => ':attribute tidak boleh diawali dengan salah satu dari berikut: :values.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'ends_with' => ':attribute harus diakhiri dengan salah satu dari berikut: :values.',
    'enum' => ':attribute yang dipilih tidak valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'extensions' => 'Bidang :attribute harus memiliki salah satu ekstensi berikut: :values.',
    'file' => ':attribute harus berupa berkas.',
    'filled' => 'Bidang :attribute harus memiliki nilai.',
    'gt' => [
        'array' => ':attribute harus memiliki lebih dari :value anggota.',
        'file' => 'Ukuran berkas :attribute harus lebih besar dari :value kilobita.',
        'numeric' => ':attribute harus lebih besar dari :value.',
        'string' => ':attribute harus lebih besar dari :value karakter.',
    ],
    'gte' => [
        'array' => ':attribute harus memiliki setidaknya :value anggota.',
        'file' => 'Ukuran berkas :attribute harus lebih besar dari atau sama dengan :value kilobita.',
        'numeric' => ':attribute harus lebih besar dari atau sama dengan :value.',
        'string' => ':attribute harus berisi minimal :value karakter.',
    ],
    'hex_color' => 'Bidang :attribute harus berupa warna heksadesimal yang valid.',
    'image' => ':attribute harus berupa gambar.',
    'in' => ':attribute yang dipilih tidak valid.',
    'in_array' => 'Bidang :attribute harus ada di dalam :other.',
    'in_array_keys' => 'Bidang :attribute harus memuat setidaknya salah satu kunci berikut: :values.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'ip' => ':attribute harus berupa alamat IP yang valid.',
    'ipv4' => ':attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => ':attribute harus berupa alamat IPv6 yang valid.',
    'json' => ':attribute harus berupa string JSON yang valid.',
    'list' => 'Bidang :attribute harus berupa daftar (list).',
    'lowercase' => ':attribute harus berupa huruf kecil.',
    'lt' => [
        'array' => ':attribute harus memiliki kurang dari :value anggota.',
        'file' => 'Ukuran berkas :attribute harus kurang dari :value kilobita.',
        'numeric' => ':attribute harus kurang dari :value.',
        'string' => ':attribute harus berisi kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => ':attribute tidak boleh memiliki lebih dari :value anggota.',
        'file' => 'Ukuran berkas :attribute tidak boleh lebih dari :value kilobita.',
        'numeric' => ':attribute harus bernilai kurang dari atau sama dengan :value.',
        'string' => ':attribute harus berisi maksimal :value karakter.',
    ],
    'mac_address' => ':attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => ':attribute tidak boleh memiliki lebih dari :max anggota.',
        'file' => 'Ukuran berkas :attribute tidak boleh lebih dari :max kilobita.',
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
    ],
    'max_digits' => ':attribute tidak boleh memiliki lebih dari :max digit.',
    'mimes' => ':attribute harus berupa berkas berjenis: :values.',
    'mimetypes' => ':attribute harus berupa berkas berjenis: :values.',
    'min' => [
        'array' => ':attribute harus memiliki minimal :min anggota.',
        'file' => 'Ukuran berkas :attribute minimal harus :min kilobita.',
        'numeric' => ':attribute minimal harus bernilai :min.',
        'string' => ':attribute minimal harus berisi :min karakter.',
    ],
    'min_digits' => ':attribute harus memiliki minimal :min digit.',
    'missing' => 'Bidang :attribute harus tidak ada.',
    'missing_if' => 'Bidang :attribute harus tidak ada ketika :other bernilai :value.',
    'missing_unless' => 'Bidang :attribute harus tidak ada kecuali :other bernilai :value.',
    'missing_with' => 'Bidang :attribute harus tidak ada jika terdapat :values.',
    'missing_with_all' => 'Bidang :attribute harus tidak ada jika terdapat :values.',
    'multiple_of' => ':attribute harus merupakan kelipatan dari :value.',
    'not_in' => ':attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :attribute tidak valid.',
    'numeric' => ':attribute harus berupa angka.',
    'password' => [
        'letters' => ':attribute harus mengandung setidaknya satu huruf.',
        'mixed' => ':attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => ':attribute harus mengandung setidaknya satu angka.',
        'symbols' => ':attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => ':attribute yang dimasukkan terdeteksi dalam kebocoran data. Silakan gunakan :attribute yang lain.',
    ],
    'present' => 'Bidang :attribute harus ada.',
    'present_if' => 'Bidang :attribute harus ada ketika :other bernilai :value.',
    'present_unless' => 'Bidang :attribute harus ada kecuali :other bernilai :value.',
    'present_with' => 'Bidang :attribute harus ada ketika :values ada.',
    'present_with_all' => 'Bidang :attribute harus ada ketika semua :values ada.',
    'prohibited' => 'Bidang :attribute dilarang diisi.',
    'prohibited_if' => 'Bidang :attribute dilarang diisi ketika :other bernilai :value.',
    'prohibited_if_accepted' => 'Bidang :attribute dilarang diisi ketika :other diterima.',
    'prohibited_if_declined' => 'Bidang :attribute dilarang diisi ketika :other ditolak.',
    'prohibited_unless' => 'Bidang :attribute dilarang diisi kecuali :other bernilai :values.',
    'prohibits' => 'Bidang :attribute melarang :other untuk ada.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => 'Bidang :attribute wajib diisi.',
    'required_array_keys' => 'Bidang :attribute harus berisi entri untuk: :values.',
    'required_if' => 'Bidang :attribute wajib diisi bila :other bernilai :value.',
    'required_if_accepted' => 'Bidang :attribute wajib diisi bila :other diterima.',
    'required_if_declined' => 'Bidang :attribute wajib diisi bila :other ditolak.',
    'required_unless' => 'Bidang :attribute wajib diisi kecuali :other memiliki nilai :values.',
    'required_with' => 'Bidang :attribute wajib diisi jika terdapat :values.',
    'required_with_all' => 'Bidang :attribute wajib diisi jika terdapat semua :values.',
    'required_without' => 'Bidang :attribute wajib diisi jika tidak terdapat :values.',
    'required_without_all' => 'Bidang :attribute wajib diisi jika sama sekali tidak ada :values.',
    'same' => ':attribute dan :other harus cocok.',
    'size' => [
        'array' => ':attribute harus mengandung :size anggota.',
        'file' => 'Ukuran berkas :attribute harus berukuran :size kilobita.',
        'numeric' => ':attribute harus berukuran :size.',
        'string' => ':attribute harus berisi :size karakter.',
    ],
    'starts_with' => ':attribute harus diawali dengan salah satu dari berikut: :values.',
    'string' => ':attribute harus berupa teks.',
    'timezone' => ':attribute harus berupa zona waktu yang valid.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => 'Berkas :attribute gagal diunggah.',
    'uppercase' => ':attribute harus berupa huruf kapital.',
    'url' => ':attribute harus berupa URL yang valid.',
    'ulid' => ':attribute harus berupa ULID yang valid.',
    'uuid' => ':attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi Kustom
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atribut Kustom
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'file' => 'berkas',
        'files' => 'berkas',
        'file_path' => 'berkas materi',
        'email' => 'alamat email',
        'password' => 'kata sandi',
        'title' => 'judul',
        'name' => 'nama lengkap',
        'phone' => 'nomor WhatsApp',
        'nik' => 'NIK',
    ],

];
