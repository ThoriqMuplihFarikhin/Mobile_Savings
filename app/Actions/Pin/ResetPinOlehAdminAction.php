<?php

namespace App\Actions\Pin;

use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Support\Pin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetPinOlehAdminAction
{
    /**
     * Reset PIN oleh admin: ganti ke PIN acak yang bukan PIN lemah,
     * paksa ganti PIN pada login berikutnya, cabut seluruh sesi target,
     * dan catat log tanpa menyimpan PIN.
     *
     * @throws ValidationException bila target bukan nasabah atau kolektor
     */
    public function execute(User $target): string
    {
        if (! in_array($target->role, ['nasabah', 'kolektor'], true)) {
            throw ValidationException::withMessages([
                'reset_pin' => 'Hanya nasabah atau kolektor yang dapat direset PIN-nya.',
            ]);
        }

        do {
            $pin = (string) random_int(100000, 999999);
        } while (Pin::lemah($pin));

        $target->update([
            'pin_hash' => Hash::make($pin),
            'harus_ganti_pin' => true,
            'percobaan_gagal' => 0,
            'login_terkunci_hingga' => null,
        ]);

        DB::table('sessions')
            ->where('user_id', $target->id)
            ->delete();

        ActivityLogger::log('reset_pin', 'users', $target->id);

        return $pin;
    }
}
