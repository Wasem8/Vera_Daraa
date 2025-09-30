<?php
namespace App\Repositories;

use App\Models\ResetCodePassword;

class PasswordResetRepository
{
    public function create(string $email, string $code): ResetCodePassword
    {

        ResetCodePassword::where('email', $email)->delete();


        return ResetCodePassword::create([
            'email' => $email,
            'code'  => $code,
        ]);
    }

    public function findByCode(string $code): ?ResetCodePassword
    {
        return ResetCodePassword::firstWhere('code', $code);
    }

    public function delete(ResetCodePassword $record): void
    {
        $record->delete();
    }
}

