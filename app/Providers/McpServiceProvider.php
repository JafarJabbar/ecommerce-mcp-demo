<?php

namespace App\Providers;

use App\Mcp\Tools\EcommerceStatisticsTool;
use Illuminate\Support\ServiceProvider;
use Laravel\Mcp\Server\Facades\Mcp;

class McpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
