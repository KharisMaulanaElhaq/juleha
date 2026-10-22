<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'question',
        'answer',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Resolusi dinamis placeholder pengaturan pada jawaban FAQ.
     */
    public function getAnswerAttribute(?string $value): string
    {
        $val = $value ?? '';
        $issuer = (string) setting('cert_issuer_name', 'LSP Pertanian RI');
        $years = (string) setting('cert_validity_years', '3');
        $formNote = (string) setting('cert_form_note', 'digital');

        if (str_contains($val, '{cert_issuer_name}') || str_contains($val, '{cert_validity_years}') || str_contains($val, '{cert_form_note}')) {
            return str_replace(
                ['{cert_issuer_name}', '{cert_validity_years}', '{cert_form_note}'],
                [$issuer, $years, $formNote],
                $val
            );
        }

        if (isset($this->attributes['question'])) {
            if ($this->attributes['question'] === 'Siapa yang menerbitkan sertifikat?') {
                return "Sertifikat kompetensi diterbitkan secara resmi oleh {$issuer} sebagai lembaga sertifikasi profesi yang berwenang, sedangkan DPD JULEHA Kabupaten Brebes bertindak memfasilitasi pelaksanaan kegiatan.";
            }

            if ($this->attributes['question'] === 'Berapa lama sertifikat berlaku dan apa bentuknya?') {
                return "Sertifikat kompetensi BNSP berlaku selama {$years} tahun sejak tanggal terbit dan diterbitkan dalam bentuk {$formNote}.";
            }
        }

        return $val;
    }

    public function getParsedAnswerAttribute(): string
    {
        return $this->answer;
    }
}
