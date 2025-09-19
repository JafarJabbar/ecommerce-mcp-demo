{{-- resources/views/auth/2fa-setup.blade.php --}}
@extends('layouts.app')

@section('title', 'Setup 2FA')

@section('content')
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <i class="fas fa-shield-alt text-4xl text-blue-500 mb-4"></i>
                <h2 class="text-2xl font-bold">Setup Two-Factor Authentication</h2>
                <p class="text-gray-600 mt-2">Scan the QR code with your authenticator app</p>
            </div>

            <div class="text-center mb-6">
                <div class="inline-block p-4 bg-white border-2 border-gray-300 rounded-lg">
                    {!! $qrCode !!}
                </div>
            </div>

            <div class="mb-6 p-4 bg-gray-100 rounded-lg">
                <p class="text-sm text-gray-700 mb-2">Can't scan? Enter this code manually:</p>
                <p class="font-mono text-sm break-all bg-white p-2 rounded border">{{ $secret }}</p>
            </div>

            <form method="POST" action="{{ route('2fa.confirm') }}">
                @csrf

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-key mr-2"></i>Enter 6-digit code from your app
                    </label>
                    <input type="text" name="code" maxlength="6" required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500 text-center text-2xl tracking-widest @error('code') border-red-500 @enderror"
                           placeholder="000000">
                    @error('code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                    <i class="fas fa-check mr-2"></i>Verify & Enable 2FA
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:underline">
                    Skip for now
                </a>
            </div>
        </div>
    </div>
@endsection
