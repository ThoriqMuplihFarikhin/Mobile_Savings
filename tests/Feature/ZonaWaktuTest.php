<?php

use Illuminate\Support\Carbon;

afterEach(fn () => Carbon::setTestNow());

it('menjalankan aplikasi pada zona waktu bisnis Asia/Jakarta', function () {
    expect(config('app.timezone'))->toBe('Asia/Jakarta');
    expect(now()->timezoneName)->toBe('Asia/Jakarta');
});

it('menggunakan tanggal Jakarta setelah tengah malam UTC', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-30 17:30:00', 'UTC'));

    expect(now()->timezoneName)->toBe('Asia/Jakarta');
    expect(now()->toDateString())->toBe('2026-10-01');
    expect(now()->format('Y-m-d H:i:s'))->toBe('2026-10-01 00:30:00');
});

it('menggunakan tanggal Jakarta pada pagi hari UTC', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-30 04:00:00', 'UTC'));

    expect(now()->format('Y-m-d H:i:s'))->toBe('2026-09-30 11:00:00');
    expect(now()->toDateString())->toBe('2026-09-30');
});
