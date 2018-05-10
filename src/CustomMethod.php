<?php declare(strict_types = 1);

namespace Weebly\PHPStan\Laravel;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class CustomMethod implements MethodReflection
{
    protected $class;
    protected $name;
    protected $returnType;

    public function __construct(
        ClassReflection $class,
        string $name,
        $returnType
    ) {
        $this->class = $class;
        $this->name = $name;
        $this->returnType = $returnType;
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->class;
    }

    public function getPrototype(): MethodReflection
    {
        return $this;
    }

    public function isStatic(): bool
    {
        return true;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \PHPStan\Reflection\ParameterReflection[]
     */
    public function getParameters(): array
    {
        return [];
    }

    public function isVariadic(): bool
    {
        return true;
    }

    public function getReturnType(): Type
    {
        if (is_array($this->returnType)) {
            return new UnionType($this->returnType);
        }

        return new ObjectType($this->returnType);
    }
}
