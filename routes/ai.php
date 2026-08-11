<?php

use App\Mcp\Servers\TemplateServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('laravel-template', TemplateServer::class);
