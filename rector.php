<?php

declare(strict_types=1);

use App\Rector\AddTypeToConstOnReadonlyClassRector;
use App\Rector\EnforceInvokableControllerRouteRector;
use App\Rector\RenameParamToMatchTypeExactCaseRector;
use Pest\Rector\Set\PestSetList;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeFromIterableMethodCallRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withRules([
        RenameVariableToMatchNewTypeRector::class,
        RenameParamToMatchTypeExactCaseRector::class,
        AddClosureParamTypeFromIterableMethodCallRector::class,
        AddTypeToConstOnReadonlyClassRector::class,
        EnforceInvokableControllerRouteRector::class,
    ])->withSets([
        PestSetList::CODING_STYLE,
    ]);
