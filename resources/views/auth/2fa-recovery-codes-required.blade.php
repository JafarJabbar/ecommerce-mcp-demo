{{-- resources/views/auth/2fa-recovery-codes-required.blade.php --}}
@extends('layouts.app')

@section('title', 'Save Recovery Codes')

@section('content')
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <i class="fas fa-key text-4xl text-green-500 mb-4"></i>
                <h2 class="text-2xl font-bold">Save Your Recovery Codes</h2>
                <p class="text-gray-600 mt-2">These codes will allow you to access your account if you lose your authenticator</p>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-yellow-400 mt-1 mr-3"></i>
                    <div>
                        <p class="text-sm text-yellow-700">
                            <strong>Critical:</strong> Save these recovery codes in a secure location. Each code can only be used once. Without them, you may lose access to your account if you lose your authenticator device.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <div class="grid grid-cols-2 gap-2">
                    @foreach($recoveryCodes as $code)
                        <div class="bg-gray-100 p-3 rounded text-center font-mono text-sm border">
                            {{ $code }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex space-x-3 mb-4">
                <button onclick="downloadCodes()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition duration-200">
                    <i class="fas fa-download mr-2"></i>Download
                </button>
                <button onclick="printCodes()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition duration-200">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>

            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center">
                    <input type="checkbox" id="codesConfirm" class="mr-3" onchange="toggleContinueButton()">
                    <label for="codesConfirm" class="text-sm text-blue-700">
                        I have saved these recovery codes in a secure location
                    </label>
                </div>
            </div>

            <div class="text-center">
                <button id="continueBtn" disabled onclick="location.href='{{ route('dashboard') }}'"
                        class="bg-gray-300 text-gray-500 px-8 py-3 rounded-lg cursor-not-allowed transition duration-200">
                    <i class="fas fa-arrow-right mr-2"></i>Continue to Dashboard
                </button>
            </div>
        </div>
    </div>

    <script>
        function downloadCodes() {
            const codes = @json($recoveryCodes);
            const content = "ADMIN RECOVERY CODES for {{ config('app.name') }}\n" +
                "Generated: {{ now()->format('Y-m-d H:i:s') }}\n" +
                "User: {{ Auth::user()->email }}\n\n" +
                "IMPORTANT: These codes are single-use and required for admin access!\n\n" +
                codes.join('\n') +
                "\n\n⚠️  KEEP THESE CODES SAFE AND SECURE! ⚠️";

            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'admin-recovery-codes.txt';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        function printCodes() {
            const codes = @json($recoveryCodes);
            const printContent = `
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #dc2626;">ADMIN RECOVERY CODES</h1>
            <h2>{{ config('app.name') }}</h2>
            <p><strong>User:</strong> {{ Auth::user()->email }}</p>
            <p><strong>Generated:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
        <div style="background: #fee; border: 2px solid #dc2626; padding: 20px; margin: 20px 0; text-align: center;">
            <strong style="color: #dc2626;">⚠️ CRITICAL: These codes provide admin access. Keep them secure! ⚠️</strong>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 30px 0;">
            ${codes.map(code => `<div style="padding: 15px; border: 2px solid #374151; text-align: center; font-family: monospace; font-size: 16px; background: #f9fafb;">${code}</div>`).join('')}
        </div>
        <div style="margin-top: 30px; font-size: 12px; color: #666;">
            <p><strong>Instructions:</strong></p>
            <ul style="text-align: left; margin: 10px 20px;">
                <li>Each code can only be used once</li>
                <li>Use these codes if you lose access to your authenticator app</li>
                <li>Store in a secure location (safe, password manager, etc.)</li>
                <li>Generate new codes from admin dashboard if needed</li>
            </ul>
        </div>
    `;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.print();
        }

        function toggleContinueButton() {
            const checkbox = document.getElementById('codesConfirm');
            const button = document.getElementById('continueBtn');

            if (checkbox.checked) {
                button.disabled = false;
                button.className = 'bg-green-500 text-white px-8 py-3 rounded-lg hover:bg-green-600 transition duration-200 cursor-pointer';
                button.innerHTML = '<i class="fas fa-arrow-right mr-2"></i>Continue to Dashboard';
            } else {
                button.disabled = true;
                button.className = 'bg-gray-300 text-gray-500 px-8 py-3 rounded-lg cursor-not-allowed transition duration-200';
            }
        }
    </script>
@endsection
