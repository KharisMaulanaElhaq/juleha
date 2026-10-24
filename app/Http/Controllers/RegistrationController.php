<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Event;
use App\Models\Registration;
use App\Services\RegistrationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public const BREBES_DISTRICTS = [
        'Banjarkharjo',
        'Brebes',
        'Bulakamba',
        'Bumiayu',
        'Jatibarang',
        'Kersana',
        'Ketanggungan',
        'Larangan',
        'Losari',
        'Paguyangan',
        'Salem',
        'Sirampog',
        'Songgom',
        'Tanjung',
        'Tonjong',
        'Wanasari',
        'Luar Kabupaten Brebes',
    ];

    public function create(string $slug): View
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        return view('pages.bimtek-daftar', [
            'event' => $event,
            'districts' => self::BREBES_DISTRICTS,
            'participantTypes' => Registration::participantTypeLabels(),
        ]);
    }

    public function store(StoreRegistrationRequest $request, string $slug, RegistrationService $service): RedirectResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        try {
            $registration = $service->register($event, $request->validated());

            session(['last_registration_code' => $registration->code]);

            return redirect()->route('registration.success')
                ->with('registered_code', $registration->code);
        } catch (DomainException $e) {
            $msg = $e->getMessage();
            $field = str_contains($msg, 'WhatsApp') || str_contains($msg, 'terdaftar') ? 'phone' : 'registration_error';

            return back()
                ->withInput()
                ->with('error', $msg)
                ->withErrors([$field => $msg, 'registration_error' => $msg]);
        }
    }

    public function success(): View
    {
        $code = session('last_registration_code') ?? request('code');
        $registration = null;

        if ($code) {
            $registration = Registration::with('event')->where('code', $code)->first();
        }

        $waUrl = null;
        if ($registration) {
            $eventTitle = $registration->event?->title ?? 'Bimtek JULEHA Brebes';
            $msg = "Halo Admin DPD JULEHA Brebes, saya telah mendaftar Bimtek dengan kode registrasi *{$registration->code}* untuk kegiatan *{$eventTitle}*. Mohon konfirmasi dan informasi berikutnya. Terima kasih.";
            $waUrl = setting_wa_link($msg);
        }

        return view('pages.pendaftaran-sukses', [
            'registration' => $registration,
            'adminPhone' => setting_wa_display('whatsapp_number'),
            'waUrl' => $waUrl,
        ]);
    }
}
