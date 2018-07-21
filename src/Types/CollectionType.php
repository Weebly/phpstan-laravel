<?php

namespace Weebly\PHPStan\Laravel\Types;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use PHPStan\Type\IterableType;

class CollectionType extends UnionType
{
    public function __construct()
    {
        parent::__construct([
            new ObjectType(Collection::class),
            new IterableType(new IntegerType(), new ObjectType(Model::class)),
        ]);
    }
}
