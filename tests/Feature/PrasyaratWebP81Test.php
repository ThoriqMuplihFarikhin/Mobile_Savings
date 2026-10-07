<?php

use App\Models\User;

it('memuat leaflet dan signature_pad lewat bundel vite di halaman absen', function () {
    $this->actingAs(User::factory()->kolektor()->create());

    $respons = $this->get(route('kolektor.absen.index'))->assertOk();

    $respons->assertDontSee('unpkg.com');
    $respons->assertDontSee('cdn.jsdelivr.net');

    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    $js = '';
    $css = '';
    foreach ($manifest as $entri) {
        if (isset($entri['file']) && str_ends_with($entri['file'], '.js')) {
            $js .= file_get_contents(public_path('build/'.$entri['file']));
        }
        foreach ($entri['css'] ?? [] as $gaya) {
            $css .= file_get_contents(public_path('build/'.$gaya));
        }
    }

    expect($js)->toContain('SignaturePad');
    expect($css)->toContain('leaflet-container');
});
