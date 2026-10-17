<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'participant_type' => ['required', 'string', 'in:calon_juleha,juleha_aktif,pengurus_masjid,pekerja_rph,lainnya'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'consent' => ['required', 'accepted'],
            'website_hp' => ['nullable', 'max:0'], // Honeypot
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.min' => 'Nomor WhatsApp minimal 9 digit.',
            'district.required' => 'Kecamatan domisili wajib dipilih / diisi.',
            'participant_type.required' => 'Kategori peserta wajib dipilih.',
            'participant_type.in' => 'Kategori peserta tidak valid.',
            'consent.required' => 'Anda harus menyetujui pemrosesan data pribadi untuk pendaftaran bimtek.',
            'consent.accepted' => 'Anda harus menyetujui pemrosesan data pribadi untuk pendaftaran bimtek.',
            'website_hp.max' => 'Spam terdeteksi.',
        ];
    }
}
