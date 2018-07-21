<?php

namespace Weebly\PHPStan\Laravel\Types;

use Illuminate\Database\Eloquent\Builder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VoidType;
use PHPStan\Type\ClosureType;
use PHPStan\Reflection\PassedByReference;

class WhereClosureType extends ClosureType
{
    public function __construct()
    {
        parent::__construct([
            'query',
            false,
            new ObjectType(Builder::class),
            PassedByReference::createNo(),
            false,
        ], new VoidType(), false);
    }
}
