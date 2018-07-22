<?php declare(strict_types = 1);

namespace Weebly\PHPStan\Laravel;

use PHPStan\Reflection\Php\NativeBuiltinMethodReflection;

final class ReflectionMethodAlwaysStatic extends NativeBuiltinMethodReflection
{
    public function isStatic(): bool
    {
        return true;
    }
}
