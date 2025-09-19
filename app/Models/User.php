<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PragmaRX\Google2FA\Google2FA;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'two_factor_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    /**
     * Check if user has enabled two-factor authentication.
     */
    public function hasEnabledTwoFactor(): bool
    {
        return !is_null($this->two_factor_confirmed_at) && $this->two_factor_enabled;
    }

    /**
     * Generate a new two-factor authentication secret.
     */
    public function generateTwoFactorSecret(): string
    {
        $google2fa = new Google2FA();
        $this->two_factor_secret = $google2fa->generateSecretKey();
        $this->save();

        return $this->two_factor_secret;
    }

    /**
     * Get the QR code URL for the user's two-factor authentication secret.
     */
    public function getTwoFactorQrCodeUrl(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $this->two_factor_secret
        );
    }

    /**
     * Verify a two-factor authentication code.
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        $google2fa = new Google2FA();
        return $google2fa->verifyKey($this->two_factor_secret, $code);
    }

    /**
     * Generate new recovery codes.
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        }

        $this->two_factor_recovery_codes = $codes;
        $this->save();

        return $codes;
    }

    /**
     * Check if a recovery code is valid and consume it.
     */
    public function useRecoveryCode(string $code): bool
    {
        $recoveryCodes = $this->two_factor_recovery_codes ?? [];
        $codeUpper = strtoupper($code);

        if (in_array($codeUpper, $recoveryCodes)) {
            $this->two_factor_recovery_codes = array_values(
                array_diff($recoveryCodes, [$codeUpper])
            );
            $this->save();
            return true;
        }

        return false;
    }
}
