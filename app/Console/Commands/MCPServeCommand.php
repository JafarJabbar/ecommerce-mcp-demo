<?php
// app/Console/Commands/MCPServeCommand.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MCP\EcommerceMCPServer;

class MCPServeCommand extends Command
{
    protected $signature = 'mcp:serve';
    protected $description = 'Start the MCP server for ecommerce operations';

    public function handle()
    {
        $server = new EcommerceMCPServer();
        $server->run();
    }
}
