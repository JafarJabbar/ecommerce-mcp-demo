{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="w-full max-w-4xl">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <i class="fas fa-tachometer-alt text-4xl text-blue-500 mb-4"></i>
                <h1 class="text-3xl font-bold">Welcome, {{ $user->name }}!</h1>
                <p class="text-gray-600 mt-2">Manage your account security settings</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                {{-- Account Information --}}
                <div class="border rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">
                        <i class="fas fa-user text-blue-500 mr-2"></i>Account Information
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <p class="text-gray-900">{{ $user->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="text-gray-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Member since</label>
                            <p class="text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Two-Factor Authentication --}}
                <div class="border rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">
                        <i class="fas fa-shield-alt text-green-500 mr-2"></i>Two-Factor Authentication
                    </h3>

                    @if($user->hasEnabledTwoFactor())
                        <div class="mb-4">
                            <div class="flex items-center text-green-600 mb-2">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span class="font-medium">2FA is enabled</span>
                            </div>
                            <p class="text-sm text-gray-600">Your account is protected with two-factor authentication.</p>
                        </div>

                        <div class="space-y-3">
                            <a href="{{ route('2fa.recovery-codes') }}"
                               class="block w-full bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600 transition duration-200">
                                <i class="fas fa-key mr-2"></i>View Recovery Codes
                            </a>

                            <button onclick="showDisable2FA()"
                                    class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600 transition duration-200">
                                <i class="fas fa-times mr-2"></i>Disable 2FA
                            </button>
                        </div>
                    @else
                        <div class="mb-4">
                            <div class="flex items-center text-yellow-600 mb-2">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <span class="font-medium">2FA is not enabled</span>
                            </div>
                            <p class="text-sm text-gray-600">Add an extra layer of security to your account.</p>
                        </div>

                        <a href="{{ route('2fa.enable') }}"
                           class="block w-full bg-green-500 text-white text-center py-2 rounded hover:bg-green-600 transition duration-200">
                            <i class="fas fa-shield-alt mr-2"></i>Enable 2FA
                        </a>
                    @endif
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="mt-8 border rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-4">
                    <i class="fas fa-history text-purple-500 mr-2"></i>Security Status
                </h3>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <i class="fas fa-check-circle text-2xl text-green-500 mb-2"></i>
                        <h4 class="font-medium">Account Verified</h4>
                        <p class="text-sm text-gray-600">Your email is verified</p>
                    </div>
                    <div class="text-center p-4 {{ $user->hasEnabledTwoFactor() ? 'bg-green-50' : 'bg-yellow-50' }} rounded-lg">
                        <i class="fas fa-shield-alt text-2xl {{ $user->hasEnabledTwoFactor() ? 'text-green-500' : 'text-yellow-500' }} mb-2"></i>
                        <h4 class="font-medium">Two-Factor Auth</h4>
                        <p class="text-sm text-gray-600">{{ $user->hasEnabledTwoFactor() ? 'Enabled' : 'Not enabled' }}</p>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <i class="fas fa-lock text-2xl text-blue-500 mb-2"></i>
                        <h4 class="font-medium">Secure Connection</h4>
                        <p class="text-sm text-gray-600">SSL encrypted</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Disable 2FA Modal --}}
    @if($user->hasEnabledTwoFactor())
        <div id="disable2FAModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                    <h3 class="text-lg font-semibold mb-4">Disable Two-Factor Authentication</h3>
                    <p class="text-gray-600 mb-4">Enter your current password to disable 2FA:</p>

                    <form method="POST" action="{{ route('2fa.disable') }}">
                        @csrf
                        <div class="mb-4">
                            <input type="password" name="current_password" required
                                   placeholder="Current password"
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                        </div>

                        <div class="flex space-x-3">
                            <button type="button" onclick="hideDisable2FA()"
                                    class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="flex-1 bg-red-500 text-white py-2 rounded-lg hover:bg-red-600">
                                Disable 2FA
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        function showDisable2FA() {
            document.getElementById('disable2FAModal').classList.remove('hidden');
        }

        function hideDisable2FA() {
            document.getElementById('disable2FAModal').classList.add('hidden');
        }
    </script>
@endsection
