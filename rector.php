<?php

declare(strict_types=1);

use App\Rector\AddTypeToConstOnReadonlyClassRector;
use App\Rector\RenameParamToMatchTypeExactCaseRector;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeFromIterableMethodCallRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/tests',
    ])
    ->withRules([
        RenameVariableToMatchNewTypeRector::class,
        RenameParamToMatchTypeExactCaseRector::class,
        AddClosureParamTypeFromIterableMethodCallRector::class,
        AddTypeToConstOnReadonlyClassRector::class,
    ]);
