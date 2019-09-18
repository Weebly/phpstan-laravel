<?php

namespace Weebly\PHPStan\Laravel\Utils;

use PHPStan\Reflection\ClassReflection;

class AnnotationsHelper
{
    /**
     * Resolve class mixins from doc block
     *
     * @param ClassReflection $reflection
     * @return array
     */
    public function getMixins(ClassReflection $reflection) : array
    {
        $mixinResults = [ [] ];

        $currentReflection = $reflection->getNativeReflection();
        do {
            preg_match_all(
                '/@mixin\s+([\w\\\\]+)/',
                (string) $currentReflection->getDocComment(),
                $matches
            );

            $mixinResults[] = array_map(function ($mixin) {
                return preg_replace('#^\\\\#', '', $mixin);
            }, $matches[1]);
        } while ($currentReflection = $currentReflection->getParentClass());

        return array_merge(...$mixinResults);
    }
}
