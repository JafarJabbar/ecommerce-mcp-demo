<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Mcp\Tools\EcommerceStatisticsTool;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('home');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// 2FA verification routes (accessible when logged out with session)
Route::middleware('web')->group(function () {
    Route::get('/2fa/verify', [TwoFactorController::class, 'showVerify'])->name('2fa.verify');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.post');
});

// Mandatory 2FA setup routes (only for authenticated users without 2FA)
Route::middleware('auth')->group(function () {
    Route::get('/2fa/setup-required', [TwoFactorController::class, 'setupRequired'])->name('2fa.setup.required');
    Route::post('/2fa/confirm-required', [TwoFactorController::class, 'confirmRequired'])->name('2fa.confirm.required');
});

// Authenticated routes with mandatory 2FA enforcement
Route::middleware(['auth', '2fa'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2FA management routes (disable functionality removed)
    Route::prefix('2fa')->name('2fa.')->group(function () {
        Route::get('/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('recovery-codes');
        // Note: enable and disable routes removed since 2FA is mandatory
    });
});



/*
|--------------------------------------------------------------------------
| AI/MCP Routes
|--------------------------------------------------------------------------
|
| Here is where you can register AI and MCP related routes for your
| application. These routes are loaded by the RouteServiceProvider
| within a group which contains the "api" middleware group.
|
*/

// MCP Server endpoints
Route::prefix('mcp')->name('mcp.')->group(function () {

    // Server capabilities and info
    Route::get('/', function () {
        return response()->json([
            'jsonrpc' => '2.0',
            'result' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [
                    'tools' => [
                        [
                            'name' => 'ecommerce_statistics',
                            'description' => 'E-commerce mağaza statistikaları və analitikası əldə etmək üçün tool',
                            'inputSchema' => [
                                'type' => 'object',
                                'properties' => [
                                    'action' => [
                                        'type' => 'string',
                                        'enum' => [
                                            'sales_summary',
                                            'top_products',
                                            'customer_stats',
                                            'category_performance',
                                            'monthly_revenue',
                                            'order_trends',
                                            'low_stock_products'
                                        ]
                                    ],
                                    'limit' => ['type' => 'integer', 'default' => 10],
                                    'period' => ['type' => 'string', 'default' => 'month']
                                ],
                                'required' => ['action']
                            ]
                        ]
                    ]
                ],
                'serverInfo' => [
                    'name' => 'Laravel E-commerce MCP Server',
                    'version' => '1.0.0'
                ]
            ]
        ]);
    })->name('info');

    // Initialize connection
    Route::post('/initialize', function (Request $request) {
        return response()->json([
            'jsonrpc' => '2.0',
            'result' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [
                    'tools' => []
                ],
                'serverInfo' => [
                    'name' => 'Laravel E-commerce MCP Server',
                    'version' => '1.0.0'
                ]
            ]
        ]);
    })->name('initialize');

    // List available tools
    Route::post('/tools/list', function () {
        return response()->json([
            'jsonrpc' => '2.0',
            'result' => [
                'tools' => [
                    [
                        'name' => 'ecommerce_statistics',
                        'description' => 'E-commerce mağaza statistikaları və analitikası',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'action' => [
                                    'type' => 'string',
                                    'enum' => [
                                        'sales_summary',
                                        'top_products',
                                        'customer_stats',
                                        'category_performance',
                                        'monthly_revenue',
                                        'order_trends',
                                        'low_stock_products'
                                    ]
                                ],
                                'limit' => ['type' => 'integer', 'default' => 10],
                                'period' => ['type' => 'string', 'default' => 'month']
                            ],
                            'required' => ['action']
                        ]
                    ]
                ]
            ]
        ]);
    })->name('tools.list');

    // Execute tool
    Route::post('/tools/call', function (Request $request) {
        $toolName = $request->input('params.name');
        $arguments = $request->input('params.arguments', []);

        if ($toolName === 'ecommerce_statistics') {
            try {
                $tool = new EcommerceStatisticsTool();
                $result = $tool->handle($arguments);

                return response()->json([
                    'jsonrpc' => '2.0',
                    'result' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                            ]
                        ]
                    ]
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32603,
                        'message' => 'Daxili xəta: ' . $e->getMessage()
                    ]
                ], 500);
            }
        }

        return response()->json([
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32601,
                'message' => 'Naməlum tool: ' . $toolName
            ]
        ], 404);
    })->name('tools.call');

    // Health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'server' => 'Laravel E-commerce MCP Server',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
            'tools' => ['ecommerce_statistics']
        ]);
    })->name('health');
});

// Direct API endpoints for testing
Route::prefix('api/ecommerce')->name('api.ecommerce.')->group(function () {

    Route::post('/statistics', function (Request $request) {
        try {
            $tool = new EcommerceStatisticsTool();
            $result = $tool->handle($request->all());
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('statistics');

});
