<?php

namespace Tests\Unit\Rector\AddTypeToConstOnReadonlyClassRector\Fixture;

readonly class SomeModel
{
    public const name = 'name';
    public const count = 42;
    public const rate = 3.14;
    public const tags = [];
    public const active = true;
}

?>
-----
<?php

namespace Tests\Unit\Rector\AddTypeToConstOnReadonlyClassRector\Fixture;

readonly class SomeModel
{
    public const string name = 'name';
    public const int count = 42;
    public const float rate = 3.14;
    public const array tags = [];
    public const bool active = true;
}

?>
