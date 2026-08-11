<?php

declare(strict_types=1);

namespace App\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Controllers are invokable: a route maps to a class, never to a method on one.
 *
 * `[Controller::class, '__invoke']` is the same route written the long way, so it is
 * rewritten to `Controller::class`. Every other action that names a method — an array
 * callable, an `@` string, `Route::resource()`, `Route::controller()` — has no invokable
 * equivalent to rewrite it to and is reported as an error instead.
 */
final class EnforceInvokableControllerRouteRector extends AbstractRector
{
    /**
     * Route registration methods, mapped to the argument position holding the action.
     */
    private const array ACTION_ARGUMENT_POSITIONS = [
        'get' => 1,
        'post' => 1,
        'put' => 1,
        'patch' => 1,
        'delete' => 1,
        'options' => 1,
        'any' => 1,
        'match' => 2,
        'fallback' => 0,
    ];

    /**
     * Route registration methods that map many methods of one controller by definition.
     */
    private const array METHOD_MAPPING_REGISTRARS = [
        'resource',
        'resources',
        'apiResource',
        'apiResources',
        'singleton',
        'singletons',
        'apiSingleton',
        'apiSingletons',
        'controller',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Routes must map to an invokable controller class, never to a method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    Route::get('/user', [UserShowController::class, '__invoke']);
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    Route::get('/user', UserShowController::class);
                    CODE_SAMPLE,
            ),
        ]);
    }

    /** @return array<class-string<Node>> */
    public function getNodeTypes(): array
    {
        return [StaticCall::class, MethodCall::class];
    }

    /**
     * @param  StaticCall|MethodCall  $node
     *
     * @throws ShouldNotHappenException
     */
    public function refactor(Node $node): StaticCall|MethodCall|null
    {
        $method_name = $this->getName($node->name);
        if (! is_string($method_name)) {
            return null;
        }

        if (in_array($method_name, self::METHOD_MAPPING_REGISTRARS, true) && $this->isRouteRegistration($node)) {
            throw new ShouldNotHappenException(
                sprintf(
                    'Route::%s() maps a controller\'s methods. Register one route per invokable controller instead. See %s',
                    $method_name,
                    $this->describeLocation($node),
                )
            );
        }

        $position = self::ACTION_ARGUMENT_POSITIONS[$method_name] ?? null;
        if ($position === null || ! $this->isRouteRegistration($node)) {
            return null;
        }

        $Arg = $node->args[$position] ?? null;
        if (! $Arg instanceof Arg) {
            return null;
        }

        $Action = $this->refactorAction($Arg->value, $node);
        if (! $Action instanceof Expr) {
            return null;
        }

        $Arg->value = $Action;

        return $node;
    }

    /**
     * @throws ShouldNotHappenException
     */
    private function refactorAction(Expr $Expr, StaticCall|MethodCall $Node): ?Expr
    {
        if ($Expr instanceof Closure || $Expr instanceof ArrowFunction) {
            return null;
        }

        if ($Expr instanceof String_) {
            if (! str_contains($Expr->value, '@')) {
                return null;
            }

            throw new ShouldNotHappenException($this->violation($Expr->value, $Node));
        }

        if (! $Expr instanceof Array_) {
            return null;
        }

        if (count($Expr->items) !== 2) {
            return null;
        }

        [$ControllerItem, $MethodItem] = $Expr->items;

        if (! $MethodItem->value instanceof String_) {
            return null;
        }

        if ($MethodItem->value->value !== '__invoke') {
            throw new ShouldNotHappenException($this->violation($MethodItem->value->value, $Node));
        }

        if (! $ControllerItem->value instanceof ClassConstFetch) {
            return null;
        }

        return $ControllerItem->value;
    }

    /**
     * A route registration is either `Route::get(...)` on the facade or `get(...)` chained
     * onto a registrar returned by an earlier `Route::` call.
     */
    private function isRouteRegistration(StaticCall|MethodCall $Node): bool
    {
        if ($Node instanceof StaticCall) {
            return $this->isName($Node->class, 'Illuminate\Support\Facades\Route');
        }

        $Root = $Node->var;
        while ($Root instanceof MethodCall) {
            $Root = $Root->var;
        }

        return $Root instanceof StaticCall && $this->isName($Root->class, 'Illuminate\Support\Facades\Route');
    }

    private function violation(string $method_name, StaticCall|MethodCall $Node): string
    {
        return sprintf(
            'Route action maps to method "%s". Controllers are invokable: pass the controller class itself. See %s',
            $method_name,
            $this->describeLocation($Node),
        );
    }

    private function describeLocation(StaticCall|MethodCall $Node): string
    {
        return sprintf('%s:%d', $this->file->getFilePath(), $Node->getStartLine());
    }
}
