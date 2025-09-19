<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Ensure2FA
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        // Skip 2FA check for 2FA-related routes to prevent infinite redirects
        if ($request->routeIs('2fa.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        // If user is authenticated but doesn't have 2FA enabled, force setup
        if ($user && !$user->hasEnabledTwoFactor()) {
            return redirect()->route('2fa.setup.required')
                ->with('error', 'You must set up Two-Factor Authentication to access the admin dashboard.');
        }

        // If user has 2FA but session is not verified, require verification
        if ($user && $user->hasEnabledTwoFactor() && !session('2fa_verified')) {
            session(['2fa_user_id' => $user->id]);
            Auth::logout();
            return redirect()->route('2fa.verify')
                ->with('message', 'Please verify your two-factor authentication code.');
        }

        return $next($request);
    }}
