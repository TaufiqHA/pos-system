<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\CustomerSupportPrompt;
use App\Mcp\Resources\DashboardApp;
use App\Mcp\Resources\RecentOrdersResource;
use App\Mcp\Tools\GetProductInfoTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Pos Server')]
#[Version('0.0.1')]
#[Instructions('Instructions describing how to use the server and its features.')]
class PosServer extends Server
{
    protected array $tools = [
        GetProductInfoTool::class,
    ];

    protected array $resources = [
        RecentOrdersResource::class,
        DashboardApp::class,
    ];

    protected array $prompts = [
        CustomerSupportPrompt::class,
    ];
}
