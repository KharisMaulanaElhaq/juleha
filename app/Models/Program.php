<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'summary',
        'description',
        'icon',
        'image',
        'lsp_name',
        'features',
        'requirements',
        'how_to_join',
        'cta_type',
        'cta_text',
        'cta_whatsapp_message',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'requirements' => 'array',
            'how_to_join' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Teks pesan WhatsApp default jika belum diset secara spesifik.
     */
    public function getDefaultWhatsAppMessage(): string
    {
        if (! empty($this->cta_whatsapp_message)) {
            return $this->cta_whatsapp_message;
        }

        return "Halo Admin, saya ingin menanyakan informasi seputar {$this->title} DPD JULEHA Brebes.";
    }

    /**
     * URL tautan CTA tombol aksi program.
     */
    public function getCtaUrl(): string
    {
        if ($this->cta_type === 'bimtek') {
            return route('bimtek.index');
        }

        return setting_wa_link($this->getDefaultWhatsAppMessage());
    }

    /**
     * Label teks tombol CTA jika tidak diisi khusus.
     */
    public function getResolvedCtaText(): string
    {
        if (! empty($this->cta_text)) {
            return $this->cta_text;
        }

        return $this->cta_type === 'bimtek' ? 'Daftar Bimtek Sekarang' : 'Hubungi via WhatsApp';
    }
}
