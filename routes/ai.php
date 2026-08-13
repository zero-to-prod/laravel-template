<?php

use App\Mcp\Servers\TemplateServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('project', TemplateServer::class);
