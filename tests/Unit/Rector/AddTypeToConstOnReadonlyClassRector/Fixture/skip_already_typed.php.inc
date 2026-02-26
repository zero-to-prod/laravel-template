<?php

namespace Tests\Unit\Rector\AddTypeToConstOnReadonlyClassRector\Fixture;

readonly class AlreadyTyped
{
    public const string name = 'name';
}

