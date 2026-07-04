<?php

use App\Mcp\Servers\PosServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/pos', PosServer::class);
