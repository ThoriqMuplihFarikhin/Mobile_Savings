<?php

use App\Helpers\ActivityLogger;
use App\Jobs\KirimNotifikasiWhatsApp;
use App\Models\AdminSetting;
use App\Models\LogNotifikasi;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

it('mengantrikan pengiriman whatsapp ke job tanpa http saat request', function () {
    AdminSetting::set('wa_provider', 'wablas');
    AdminSetting::set('wa_api_url', 'https://wa.test/send-message');
    AdminSetting::set('wa_api_key', 'token-uji');

    Queue::fake();
    Http::fake();

    $nasabah = User::factory()->nasabah()->create();

    ActivityLogger::notify($nasabah->id, 'Judul', 'Pesan uji', 'whatsapp');

    Queue::assertPushed(KirimNotifikasiWhatsApp::class);
    Http::assertNothingSent();

    $log = LogNotifikasi::where('nasabah_id', $nasabah->id)
        ->where('channel', 'whatsapp')
        ->firstOrFail();

    expect($log->status_kirim)->toBe('antri');
});

it('gateway down tidak menggagalkan request pemanggil dan log menjadi gagal setelah retry habis', function () {
    AdminSetting::set('wa_provider', 'wablas');
    AdminSetting::set('wa_api_url', 'https://wa.test/send-message');
    AdminSetting::set('wa_api_key', 'token-uji');

    Queue::fake();
    Http::fake(['*' => Http::response('down', 500)]);

    $nasabah = User::factory()->nasabah()->create();

    ActivityLogger::notify($nasabah->id, 'Judul', 'Pesan uji', 'whatsapp');

    $log = LogNotifikasi::where('nasabah_id', $nasabah->id)
        ->where('channel', 'whatsapp')
        ->firstOrFail();

    $job = null;
    Queue::assertPushed(KirimNotifikasiWhatsApp::class, function ($kirim) use (&$job) {
        $job = $kirim;

        return true;
    });

    expect($log->status_kirim)->toBe('antri');

    // meniru kerja worker: setelah percobaan habis framework memanggil failed()
    try {
        $job->handle();
        throw new RuntimeException('handle seharusnya melempar saat gateway down');
    } catch (RuntimeException $e) {
        if ($e->getMessage() === 'handle seharusnya melempar saat gateway down') {
            throw $e;
        }

        $job->failed($e);
    }

    expect($log->refresh()->status_kirim)->toBe('gagal');
});

it('notify driver sync tetap berjalan walau gateway down', function () {
    AdminSetting::set('wa_provider', 'wablas');
    AdminSetting::set('wa_api_url', 'https://wa.test/send-message');
    AdminSetting::set('wa_api_key', 'token-uji');

    Http::fake(['*' => Http::response('down', 500)]);

    $nasabah = User::factory()->nasabah()->create();

    ActivityLogger::notify($nasabah->id, 'Judul', 'Pesan uji', 'whatsapp');

    $log = LogNotifikasi::where('nasabah_id', $nasabah->id)
        ->where('channel', 'whatsapp')
        ->firstOrFail();

    expect($log->status_kirim)->toBe('gagal');

    Http::assertSentCount(1);
});

it('menghormati notifikasi_wa_aktif', function () {
    Queue::fake();

    $nasabah = User::factory()->nasabah()->create(['notifikasi_wa_aktif' => false]);

    ActivityLogger::notify($nasabah->id, 'Judul', 'Pesan uji', 'both');

    Queue::assertNothingPushed();

    expect(LogNotifikasi::where('nasabah_id', $nasabah->id)->where('channel', 'whatsapp')->count())->toBe(0)
        ->and(LogNotifikasi::where('nasabah_id', $nasabah->id)->where('channel', 'in_app')->count())->toBe(1);
});

it('menandai log terkirim setelah job sukses', function () {
    AdminSetting::set('wa_provider', 'wablas');
    AdminSetting::set('wa_api_url', 'https://wa.test/send-message');
    AdminSetting::set('wa_api_key', 'token-uji');

    Queue::fake();
    Http::fake(['*' => Http::response(['status' => true])]);

    $nasabah = User::factory()->nasabah()->create();

    ActivityLogger::notify($nasabah->id, 'Judul', 'Pesan uji', 'whatsapp');

    $job = null;
    Queue::assertPushed(KirimNotifikasiWhatsApp::class, function ($kirim) use (&$job) {
        $job = $kirim;

        return true;
    });

    $job->handle();

    $log = LogNotifikasi::where('nasabah_id', $nasabah->id)
        ->where('channel', 'whatsapp')
        ->firstOrFail();

    expect($log->status_kirim)->toBe('terkirim');
});

it('tidak mencatat nomor hp penuh ke log', function () {
    AdminSetting::set('wa_provider', 'wablas');
    AdminSetting::set('wa_api_url', 'https://wa.test/send-message');
    AdminSetting::set('wa_api_key', 'token-uji');

    Http::fake(['*' => Http::response(['status' => true])]);

    Log::spy();

    app(WhatsAppService::class)->sendNotification('081234567890', 'Pesan uji');

    Log::shouldHaveReceived('info')
        ->withArgs(function (string $message, array $context) {
            return $message === 'WhatsApp message sent'
                && ($context['phone'] ?? null) === Str::mask('081234567890', '*', 4, -3);
        });
});
