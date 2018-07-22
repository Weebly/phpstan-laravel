<?php

namespace Weebly\PHPStan\Laravel\Types;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VoidType;
use PHPStan\Type\NullType;
use PHPStan\Type\IterableType;

class ModelFindReturnType implements DynamicMethodReturnTypeExtension, DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Builder::class;
    }

	public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $this->isMethodCommonSupported($methodReflection->getName());
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $this->isMethodCommonSupported($methodReflection->getName());
    }

    private function isMethodCommonSupported(string $methodName): bool
    {
        return in_array($methodName, [
            'first',
            'find',
            'get',
        ]);
    }

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        return $this->getTypeFromCommonMethodCall($methodReflection->getName(), $methodCall->args);
    }

	public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): Type
    {
        return $this->getTypeFromCommonMethodCall($methodReflection->getName(), $methodCall->args);
    }

    private function getTypeFromCommonMethodCall(string $methodName, array $args): Type
    {
        if ($methodName === 'first') {
            return new UnionType([
                new ObjectType(Model::class),
                new NullType(),
            ]);
        } elseif ($methodName === 'find') {
            if (count($args)) {
                if ($args[0]->value instanceof \PhpParser\Node\Expr\Array_) {
                    return new CollectionType();
                }
                if ($args[0]->value instanceof \PhpParser\Node\Scalar\LNumber) {
                    return new UnionType([
                        new ObjectType(Model::class),
                        new NullType(),
                    ]);
                }
            }
        } elseif ($methodName === 'get') {
            return new CollectionType();
        }

        return new VoidType();
    }

}
