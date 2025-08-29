<?php
// Test if MCP classes exist and can be instantiated
try {
    echo "Testing MCP package...\n";

    // Test if the Server class exists
    if (class_exists('Laravel\Mcp\Server')) {
        echo "✓ Laravel\Mcp\Server class found\n";
    } else {
        echo "✗ Laravel\Mcp\Server class NOT found\n";
    }

    // Test if our server class can be instantiated
    if (class_exists('App\Mcp\Servers\EcommerceServer')) {
        $server = new App\Mcp\Servers\EcommerceServer();
        echo "✓ EcommerceServer instantiated: {$server->serverName}\n";
    } else {
        echo "✗ EcommerceServer class NOT found\n";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
