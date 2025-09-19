{{-- resources/views/auth/2fa-recovery-codes.blade.php --}}
@extends('layouts.app')

@section('title', 'Recovery Codes')

@section('content')
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <i class="fas fa-key text-4xl text-green-500 mb-4"></i>
                <h2 class="text-2xl font-bold">Recovery Codes</h2>
                <p class="text-gray-600 mt-2">Save these codes in a safe place</p>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-yellow-400 mt-1 mr-3"></i>
                    <div>
                        <p class="text-sm text-yellow-700">
                            <strong>Important:</strong> These recovery codes can be used to access your account if you lose your authenticator device. Each code can only be used once.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <div class="grid grid-cols-2 gap-2">
                    @foreach($recoveryCodes as $code)
                        <div class="bg-gray-100 p-2 rounded text-center font-mono text-sm">
                            {{ $code }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex space-x-3">
                <button onclick="downloadCodes()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition duration-200">
                    <i class="fas fa-download mr-2"></i>Download
                </button>
                <button onclick="printCodes()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition duration-200">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('dashboard') }}" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition duration-200 inline-block">
                    <i class="fas fa-check mr-2"></i>Continue to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        function downloadCodes() {
            const codes = @json($recoveryCodes);
            const content = "Recovery Codes for {{ config('app.name') }}\n" +
                "Generated: {{ now()->format('Y-m-d H:i:s') }}\n\n" +
                codes.join('\n') +
                "\n\nKeep these codes safe and secure!";

            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'recovery-codes.txt';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        function printCodes() {
            const codes = @json($recoveryCodes);
            const printContent = `
        <h2>Recovery Codes for {{ config('app.name') }}</h2>
        <p>Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 20px 0;">
            ${codes.map(code => `<div style="padding: 10px; border: 1px solid #ccc; text-align: center; font-family: monospace;">${code}</div>`).join('')}
        </div>
        <p><strong>Keep these codes safe and secure!</strong></p>
    `;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.print();
        }
    </script>
@endsection
