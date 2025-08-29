<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mcp\Tools\EcommerceStatisticsTool;

class TestMcpCommand extends Command
{
    protected $signature = 'mcp:test {action} {--limit=10} {--period=month}';

    protected $description = 'MCP E-commerce toolunu test edin';

    public function handle()
    {
        $tool = new EcommerceStatisticsTool();

        $args = [
            'action' => $this->argument('action'),
            'limit' => (int) $this->option('limit'),
            'period' => $this->option('period')
        ];

        try {
            $result = $tool->handle($args);
            $this->info('MCP Tool Nəticəsi:');
            $this->info(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            $this->error('Xəta: ' . $e->getMessage());
        }
    }
}
