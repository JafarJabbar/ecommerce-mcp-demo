<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartMcpServerCommand extends Command
{
    protected $signature = 'mcp:serve {--host=0.0.0.0} {--port=3000}';

    protected $description = 'MCP server-ini başladın';

    public function handle()
    {
        $host = $this->option('host');
        $port = $this->option('port');

        $this->info("Laravel server başladılır...");
        $this->info("URL: http://{$host}:{$port}");

        // Laravel server-i başlat
        $this->call('serve', [
            '--host' => $host,
            '--port' => $port
        ]);
    }
}
