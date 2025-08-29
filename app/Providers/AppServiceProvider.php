<?php

namespace App\Providers;

use App\Mcp\Tools\ListCategoriesTool;
use App\Mcp\Tools\ListOrdersTool;
use App\Mcp\Tools\ListProductsTool;
use Illuminate\Support\ServiceProvider;
use Laravel\Mcp\Server\Facades\Mcp;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
