<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Mcp\Server\Facades\Mcp;
use App\Mcp\Tools\EcommerceStatisticsTool;
use App\Mcp\Tools\CustomerManagementTool;

class StartMcpServerCommand extends Command
{
    protected $signature = 'mcp:serve {--host=0.0.0.0} {--port=3000}';

    protected $description = 'MCP server-ini başladın';

    public function handle()
    {
        $host = $this->option('host');
        $port = (int) $this->option('port');

        $this->info("MCP Server başladılır...");
        $this->info("Host: {$host}");
        $this->info("Port: {$port}");
        $this->info("URL: http://{$host}:{$port}");

        // Tool-ları register et
        Mcp::tool(EcommerceStatisticsTool::class);
        if (class_exists(CustomerManagementTool::class)) {
            Mcp::tool(CustomerManagementTool::class);
        }

        // Server info göstər
        $this->table(
            ['Tool', 'Description'],
            [
                ['ecommerce_statistics', 'E-commerce analitika və statistika tool-u'],
                ['customer_management', 'Müştəri idarəetmə sistemi']
            ]
        );

        $this->warn('Server-i dayandırmaq üçün Ctrl+C basın');

        // Laravel MCP package-inin düzgün method-u
        try {
            // Əgər Laravel MCP package serve method-unu dəstəkləmirsə,
            // HTTP server yaradırıq
            $this->startHttpServer($host, $port);
        } catch (\Exception $e) {
            $this->error("Server başladılarkən xəta: " . $e->getMessage());
        }
    }

    private function startHttpServer($host, $port)
    {
        $this->info("HTTP Server başladılır...");

        // Simple HTTP server for MCP
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socket, $host, $port);
        socket_listen($socket);

        $this->info("Server hazırdır və dinləyir...");

        while (true) {
            $client = socket_accept($socket);
            if ($client) {
                $request = socket_read($client, 2048);
                $this->handleRequest($client, $request);
                socket_close($client);
            }
        }
    }

    private function handleRequest($client, $request)
    {
        // MCP protocol response
        $response = [
            'jsonrpc' => '2.0',
            'result' => [
                'capabilities' => [
                    'tools' => [
                        [
                            'name' => 'ecommerce_statistics',
                            'description' => 'E-commerce analitika və statistika'
                        ]
                    ]
                ]
            ]
        ];

        $json = json_encode($response);
        $httpResponse = "HTTP/1.1 200 OK\r\n";
        $httpResponse .= "Content-Type: application/json\r\n";
        $httpResponse .= "Content-Length: " . strlen($json) . "\r\n";
        $httpResponse .= "Access-Control-Allow-Origin: *\r\n";
        $httpResponse .= "\r\n";
        $httpResponse .= $json;

        socket_write($client, $httpResponse);
    }
}
