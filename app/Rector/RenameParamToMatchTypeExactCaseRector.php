<?php

declare(strict_types=1);

namespace App\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\ClassReflection;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Renames function/method parameters to match their class type hint exactly (PascalCase).
 *
 * Before: public function __construct(ViewErrorBag $errors)
 * After:  public function __construct(ViewErrorBag $ViewErrorBag)
 */
final class RenameParamToMatchTypeExactCaseRector extends AbstractRector
{
    public function __construct(
        private readonly ReflectionResolver $reflectionResolver,
    ) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename param to match class type hint exactly (PascalCase)', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        public function run(Apple $pie)
                        {
                            $food = $pie;
                        }
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        public function run(Apple $Apple)
                        {
                            $food = $Apple;
                        }
                    }
                    CODE_SAMPLE,
            ),
        ]);
    }

    /** @return array<class-string<Node>> */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }

    /** @param ClassMethod|Function_ $node */
    public function refactor(Node $node): ?Node
    {
        // Skip methods that override a parent/interface — renaming params would break the contract
        if ($node instanceof ClassMethod && $this->isOverrideMethod($node)) {
            return null;
        }

        $hasChanged = false;

        foreach ($node->params as $param) {
            if ($param->variadic) {
                continue;
            }

            if ($param->type === null) {
                continue;
            }

            // Skip promoted properties — renaming would change the property name
            if ($param instanceof Param && $param->flags !== 0) {
                continue;
            }

            $expectedName = $this->resolveExpectedName($param);

            if ($expectedName === null) {
                continue;
            }

            $currentName = $this->getName($param->var);

            if ($currentName === null || $currentName === $expectedName) {
                continue;
            }

            // Skip if another param already uses the expected name
            if ($this->hasConflictingParam($node, $expectedName, $param)) {
                continue;
            }

            $param->var = new Variable($expectedName);
            $this->renameVariableInBody($node, $currentName, $expectedName);
            $hasChanged = true;
        }

        return $hasChanged ? $node : null;
    }

    private function resolveExpectedName(Param $Param): ?string
    {
        $type = $Param->type;

        if ($type instanceof Name) {
            return $type->getLast();
        }

        if ($type instanceof Identifier) {
            // Built-in types (int, string, bool, etc.) — skip
            return null;
        }

        return null;
    }

    private function hasConflictingParam(FunctionLike $FunctionLike, string $expectedName, Param $Param): bool
    {
        foreach ($FunctionLike->getParams() as $param) {
            if ($param === $Param) {
                continue;
            }

            if ($this->getName($param->var) === $expectedName) {
                return true;
            }
        }

        return false;
    }

    private function isOverrideMethod(ClassMethod $ClassMethod): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($ClassMethod);

        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        $methodName = $this->getName($ClassMethod);

        foreach ($classReflection->getAncestors() as $ancestor) {
            if ($ancestor->getName() === $classReflection->getName()) {
                continue;
            }

            if ($ancestor->hasNativeMethod($methodName)) {
                return true;
            }
        }

        return false;
    }

    private function renameVariableInBody(FunctionLike $FunctionLike, string $oldName, string $newName): void
    {
        $stmts = $FunctionLike->getStmts();

        if ($stmts === null) {
            return;
        }

        $this->traverseNodesWithCallable($stmts, function (Node $node) use ($oldName, $newName): ?Variable {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->isName($node, $oldName)) {
                return null;
            }

            $node->name = $newName;

            return $node;
        });
    }
}