<?php

declare(strict_types=1);

use App\Helpers\DataModel;
use Pest\Rector\Set\PestSetList;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeFromIterableMethodCallRector;
use ZeroToProd\LaravelRector\Rector\AddReadonlyToClassWithTraitRector;
use ZeroToProd\LaravelRector\Rector\AddTypeToConstOnReadonlyClassRector;
use ZeroToProd\LaravelRector\Rector\EnforceControllerSuffixRector;
use ZeroToProd\LaravelRector\Rector\EnforceInvokableControllerRector;
use ZeroToProd\LaravelRector\Rector\EnforceInvokableControllerRouteRector;
use ZeroToProd\LaravelRector\Rector\ForbidTodoAnnotationRector;
use ZeroToProd\LaravelRector\Rector\RenameParamToMatchTypeExactCaseRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withRules([
        RenameVariableToMatchNewTypeRector::class,
        AddClosureParamTypeFromIterableMethodCallRector::class,
        AddTypeToConstOnReadonlyClassRector::class,
        EnforceControllerSuffixRector::class,
        EnforceInvokableControllerRector::class,
        EnforceInvokableControllerRouteRector::class,
        RenameParamToMatchTypeExactCaseRector::class,
        ForbidTodoAnnotationRector::class,
    ])
    ->withConfiguredRule(AddReadonlyToClassWithTraitRector::class, [
        'traits' => [
            DataModel::class,
        ],
    ])
    ->withSets([
        PestSetList::CODING_STYLE,
    ]);
