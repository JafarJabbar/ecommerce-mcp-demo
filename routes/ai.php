<?php

use Laravel\Mcp\Server\Facades\Mcp;

// Register the local MCP server
Mcp::local('demo', \App\Mcp\Servers\EcommerceServer::class);

// You can also register web-accessible servers if needed:
// Mcp::web('ecommerce-web', \App\Mcp\Servers\EcommerceServer::class);
