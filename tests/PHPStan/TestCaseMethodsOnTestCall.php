<?php

namespace Tests\PHPStan;

use Pest\PendingCalls\TestCall;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;
use Tests\TestCase;

final class TestCaseMethodsOnTestCall implements MethodsClassReflectionExtension
{
    public function __construct(private readonly ReflectionProvider $reflectionProvider) {}

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        return $classReflection->getName() === TestCall::class
            && $this->testCase()->hasNativeMethod($methodName);
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new PubliclyCallableMethod($this->testCase()->getNativeMethod($methodName));
    }

    private function testCase(): ClassReflection
    {
        return $this->reflectionProvider->getClass(TestCase::class);
    }
}
