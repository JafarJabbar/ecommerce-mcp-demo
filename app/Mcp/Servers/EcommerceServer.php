<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class EcommerceServer extends Server
{
    public string $serverName = 'Ecommerce Server';

    public string $serverVersion = '1.0.0';

    public string $instructions = 'Ecommerce MCP server providing tools to manage products, orders, and categories for an e-commerce application.';

    public array $tools = [
        \App\Mcp\Tools\ListCategoriesTool::class, // Start with this one
    ];

    public array $resources = [];

    public array $prompts = [];
}
