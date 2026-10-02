<?php

use App\Livewire\Admin\Pengaturan;
use App\Models\AdminSetting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('rejects backup when database driver is not mysql', function () {
    config([
        'database.default' => 'sqlite',
        'database.connections.mysql.host' => '127.0.0.1',
        'database.connections.mysql.port' => 1,
    ]);

    try {
        (new Pengaturan)->backupSekarang();
        $error = session('error');
    } finally {
        config(['database.default' => 'mysql']);
    }

    expect($error)->toContain('MySQL');
    expect(AdminSetting::get('backup_terakhir'))->toBeNull();
    expect(Storage::disk('local')->allFiles('backups'))->toBeEmpty();
});

it('builds mysqldump command without password in arguments', function () {
    config([
        'database.connections.mysql.host' => '127.0.0.1',
        'database.connections.mysql.port' => '3307',
        'database.connections.mysql.username' => 'backup_user',
        'database.connections.mysql.password' => 'super-secret-pass',
        'database.connections.mysql.database' => 'tabungan_test',
    ]);

    $method = new ReflectionMethod(Pengaturan::class, 'buildMysqldumpProcess');
    $process = $method->invoke(new Pengaturan, '/tmp/backup-tes.sql');

    expect($process->getCommandLine())
        ->not->toContain('super-secret-pass')
        ->toContain('--single-transaction')
        ->toContain('--user=backup_user')
        ->toContain('--result-file=/tmp/backup-tes.sql');
    expect($process->getEnv())->toMatchArray(['MYSQL_PWD' => 'super-secret-pass']);
});

it('creates backup, triggers download, and deletes file after send', function () {
    $admin = User::factory()->admin()->create();

    $binDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'backup-shadow-'.uniqid();
    mkdir($binDir, 0777, true);
    $originalPath = (string) getenv('PATH');
    putenv('PATH='.$binDir.PATH_SEPARATOR.$originalPath);

    if (PHP_OS_FAMILY === 'Windows') {
        file_put_contents(
            $binDir.DIRECTORY_SEPARATOR.'mysqldump.cmd',
            "@echo off\r\n".
            "setlocal EnableDelayedExpansion\r\n".
            "set \"target=\"\r\n".
            "for %%a in (%*) do (\r\n".
            "  set \"a=%%~a\"\r\n".
            "  if \"!a:~0,14!\"==\"--result-file=\" set \"target=!a:~14!\"\r\n".
            ")\r\n".
            "if \"!target!\"==\"\" exit /b 1\r\n".
            "> \"!target!\" echo -- fake mysql dump\r\n".
            "exit /b 0\r\n"
        );
    } else {
        $script = "#!/bin/sh\n".
            "for a in \"\$@\"; do\n".
            "  case \"\$a\" in\n".
            "    --result-file=*) printf '%s\\n' '-- fake mysql dump' > \"\${a#--result-file=}\";;\n".
            "  esac\n".
            "done\n".
            "exit 0\n";
        file_put_contents($binDir.DIRECTORY_SEPARATOR.'mysqldump', $script);
        chmod($binDir.DIRECTORY_SEPARATOR.'mysqldump', 0755);
    }

    try {
        $component = Livewire::actingAs($admin)->test(Pengaturan::class)
            ->call('backupSekarang');

        $component->assertFileDownloaded()
            ->assertSet('backupTerakhir', fn ($value) => $value !== null);
        expect(AdminSetting::get('backup_terakhir'))->not->toBeNull();
        expect(Storage::disk('local')->allFiles('backups'))->toBeEmpty();
        expect(glob(storage_path('app/backups/*.sql')) ?: [])->toBeEmpty();
    } finally {
        putenv('PATH='.$originalPath);
        foreach (glob($binDir.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($binDir);
    }
});
