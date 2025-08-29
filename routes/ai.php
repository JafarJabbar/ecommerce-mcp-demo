<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Mcp\Tools\EcommerceStatisticsTool;

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
