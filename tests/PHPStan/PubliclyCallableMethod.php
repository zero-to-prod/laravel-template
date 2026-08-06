<?php

namespace Tests\PHPStan;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

/** @see TestCaseMethodsOnTestCall */
final class PubliclyCallableMethod implements MethodReflection
{
    public function __construct(private readonly MethodReflection $MethodReflection) {}

    public function getDeclaringClass(): ClassReflection
    {
        return $this->MethodReflection->getDeclaringClass();
    }

    public function isStatic(): bool
    {
        return $this->MethodReflection->isStatic();
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getDocComment(): ?string
    {
        return $this->MethodReflection->getDocComment();
    }

    public function getName(): string
    {
        return $this->MethodReflection->getName();
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this->MethodReflection->getPrototype();
    }

    /** @return list<ParametersAcceptor> */
    public function getVariants(): array
    {
        return $this->MethodReflection->getVariants();
    }

    public function isDeprecated(): TrinaryLogic
    {
        return $this->MethodReflection->isDeprecated();
    }

    public function getDeprecatedDescription(): ?string
    {
        return $this->MethodReflection->getDeprecatedDescription();
    }

    public function isFinal(): TrinaryLogic
    {
        return $this->MethodReflection->isFinal();
    }

    public function isInternal(): TrinaryLogic
    {
        return $this->MethodReflection->isInternal();
    }

    public function getThrowType(): ?Type
    {
        return $this->MethodReflection->getThrowType();
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return $this->MethodReflection->hasSideEffects();
    }
}
