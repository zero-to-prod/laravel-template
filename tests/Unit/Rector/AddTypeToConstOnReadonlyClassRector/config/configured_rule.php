<?php

declare(strict_types=1);

use App\Rector\AddTypeToConstOnReadonlyClassRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        AddTypeToConstOnReadonlyClassRector::class,
    ]);
