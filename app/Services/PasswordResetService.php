<?php
namespace App\Services;

use App\Mail\SendCodeResetPassword;
use App\Models\User;
use App\Repositories\PasswordResetRepository;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    private PasswordResetRepository $repository;

    public function __construct(PasswordResetRepository $repository)
    {
        $this->repository = $repository;
    }

    public function sendCode(string $email): void
    {
        $code = mt_rand(100000, 999999);
        $record = $this->repository->create($email, $code);

        Mail::to($email)->send(new SendCodeResetPassword($record->code));
    }

    public function verifyCode(string $code): bool
    {
        $record = $this->repository->findByCode($code);

        if (!$record || $record->created_at->addMinutes(60)->isPast()) {
            if ($record) $this->repository->delete($record);
            return false;
        }

        return true;
    }

    public function resetPassword(string $code, string $newPassword): bool
    {
        $record = $this->repository->findByCode($code);

        if (!$record || $record->created_at->addMinutes(60)->isPast()) {
            if ($record) $this->repository->delete($record);
            return false;
        }

        $user = User::where('email', $record->email)->first();
        $user->update(['password' => bcrypt($newPassword)]);

        $this->repository->delete($record);

        return true;
    }
}

