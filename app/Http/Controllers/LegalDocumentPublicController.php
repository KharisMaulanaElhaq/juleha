<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Illuminate\View\View;

class LegalDocumentPublicController extends Controller
{
    /**
     * Tampilkan dokumen hukum publik berdasarkan slug.
     * Hanya dokumen dengan status 'published' yang dapat diakses publik.
     * Dokumen berstatus 'draft' langsung menghasilkan HTTP 404.
     */
    public function show(string $slug): View
    {
        $document = LegalDocument::where('slug', $slug)
            ->where('status', LegalDocument::STATUS_PUBLISHED)
            ->first();

        if (! $document) {
            abort(404, 'Dokumen hukum belum diterbitkan.');
        }

        $allPublished = LegalDocument::where('status', LegalDocument::STATUS_PUBLISHED)
            ->orderBy('id')
            ->get();

        return view('pages.legal.show', [
            'document' => $document,
            'otherDocuments' => $allPublished,
        ]);
    }

    /**
     * Tampilkan riwayat perubahan versi dokumen hukum.
     */
    public function history(string $slug): View
    {
        $document = LegalDocument::where('slug', $slug)
            ->where('status', LegalDocument::STATUS_PUBLISHED)
            ->first();

        if (! $document) {
            abort(404, 'Dokumen hukum belum diterbitkan.');
        }

        $versions = $document->versions()->latest('id')->get();

        return view('pages.legal.history', [
            'document' => $document,
            'versions' => $versions,
        ]);
    }

    public function kebijakanPrivasi(): View
    {
        return $this->show(LegalDocument::SLUG_KEBIJAKAN_PRIVASI);
    }

    public function syaratKetentuan(): View
    {
        return $this->show(LegalDocument::SLUG_SYARAT_KETENTUAN);
    }

    public function penyimpananPerangkat(): View
    {
        return $this->show(LegalDocument::SLUG_PENYIMPANAN_PERANGKAT);
    }

    public function disclaimer(): View
    {
        return $this->show(LegalDocument::SLUG_DISCLAIMER);
    }

    public function hakCipta(): View
    {
        return $this->show(LegalDocument::SLUG_HAK_CIPTA);
    }
}
