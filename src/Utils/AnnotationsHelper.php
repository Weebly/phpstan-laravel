<?php

namespace Webparking\PHPStan\Lumen\Utils;

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
        preg_match_all(
            '/@mixin\s+([\w\\\\]+)/',
            (string) $reflection->getNativeReflection()->getDocComment(),
            $mixins
        );

        return array_map(function ($mixin) {
            return preg_replace('#^\\\\#', '', $mixin);
        }, $mixins[1]);
    }
}
