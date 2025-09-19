{{-- resources/views/auth/2fa-verify.blade.php --}}
@extends('layouts.app')

@section('title', '2FA Verification')

@section('content')
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <i class="fas fa-mobile-alt text-4xl text-blue-500 mb-4"></i>
                <h2 class="text-2xl font-bold">Two-Factor Authentication</h2>
                <p class="text-gray-600 mt-2">Enter the 6-digit code from your authenticator app</p>
            </div>

            <form method="POST" action="{{ route('2fa.verify') }}">
                @csrf

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-key mr-2"></i>Authentication Code
                    </label>
                    <input type="text" name="code" maxlength="8" required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500 text-center text-2xl tracking-widest @error('code') border-red-500 @enderror"
                           placeholder="000000">
                    @error('code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-600 mt-2">
                        You can also use a recovery code (8 characters)
                    </p>
                </div>

                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>Verify & Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-gray-500 hover:underline">
                    Back to login
                </a>
            </div>
        </div>
    </div>
@endsection
