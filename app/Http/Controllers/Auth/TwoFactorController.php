<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    /**
     * Show the optional 2FA setup page.
     */
    public function enable()
    {
        $user = Auth::user();

        if ($user->hasEnabledTwoFactor()) {
            return redirect()->route('dashboard')->with('error', '2FA is already enabled.');
        }

        // Generate secret if not exists
        if (!$user->two_factor_secret) {
            $user->generateTwoFactorSecret();
        }

        $qrCodeUrl = $user->getTwoFactorQrCodeUrl();

        // Generate QR Code SVG
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('auth.2fa-setup', [
            'qrCode' => $qrCodeSvg,
            'secret' => $user->two_factor_secret
        ]);
    }

    /**
     * Show the mandatory 2FA setup page.
     */
    public function setupRequired()
    {
        $user = Auth::user();

        if ($user->hasEnabledTwoFactor()) {
            return redirect()->route('dashboard');
        }

        // Generate secret if not exists
        if (!$user->two_factor_secret) {
            $user->generateTwoFactorSecret();
        }

        $qrCodeUrl = $user->getTwoFactorQrCodeUrl();

        // Generate QR Code SVG
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('auth.2fa-setup-required', [
            'qrCode' => $qrCodeSvg,
            'secret' => $user->two_factor_secret
        ]);
    }

    /**
     * Confirm 2FA setup with verification code (optional).
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6|numeric'
        ]);

        $user = Auth::user();

        if (!$user->verifyTwoFactorCode($request->code)) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid.'
            ]);
        }

        // Enable 2FA
        $user->update([
            'two_factor_confirmed_at' => now(),
            'two_factor_enabled' => true,
        ]);

        $recoveryCodes = $user->generateRecoveryCodes();

        return view('auth.2fa-recovery-codes', [
            'recoveryCodes' => $recoveryCodes
        ])->with('success', '2FA has been enabled successfully!');
    }

    /**
     * Confirm mandatory 2FA setup with verification code.
     */
    public function confirmRequired(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6|numeric'
        ]);

        $user = Auth::user();

        if (!$user->verifyTwoFactorCode($request->code)) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid.'
            ]);
        }

        // Enable 2FA
        $user->update([
            'two_factor_confirmed_at' => now(),
            'two_factor_enabled' => true,
        ]);

        $recoveryCodes = $user->generateRecoveryCodes();

        return view('auth.2fa-recovery-codes-required', [
            'recoveryCodes' => $recoveryCodes
        ])->with('success', '2FA has been enabled successfully!');
    }

    /**
     * Show 2FA verification page.
     */
    public function showVerify()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa-verify');
    }

    /**
     * Verify 2FA code during login.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|min:6|max:8'
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please login again.');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'User not found.');
        }

        $isValidCode = false;

        // Check if it's a regular 2FA code (6 digits)
        if (strlen($request->code) === 6 && is_numeric($request->code)) {
            $isValidCode = $user->verifyTwoFactorCode($request->code);
        }
        // Check if it's a recovery code (8 characters)
        elseif (strlen($request->code) === 8) {
            $isValidCode = $user->useRecoveryCode($request->code);
        }

        if (!$isValidCode) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid.'
            ]);
        }

        // Log the user in and mark 2FA as verified
        Auth::login($user, true);
        session(['2fa_verified' => true]);
        session()->forget('2fa_user_id');

        return redirect()->intended('dashboard')
            ->with('success', 'Welcome back! You have been successfully authenticated.');
    }

    /**
     * Disable 2FA - DISABLED for mandatory 2FA system.
     */
    public function disable(Request $request)
    {
        // Disable 2FA disable functionality since it's mandatory
        return redirect()->route('dashboard')
            ->with('error', 'Two-Factor Authentication cannot be disabled as it is required for admin access.');
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes()
    {
        $user = Auth::user();

        if (!$user->hasEnabledTwoFactor()) {
            return redirect()->route('dashboard')
                ->with('error', '2FA is not enabled.');
        }

        $recoveryCodes = $user->generateRecoveryCodes();

        return view('auth.2fa-recovery-codes', [
            'recoveryCodes' => $recoveryCodes
        ])->with('success', 'New recovery codes have been generated.');
    }
}
