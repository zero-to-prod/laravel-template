<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Helpers\SvgName;
use Zerotoprod\DataModel\Describe;

class Svg
{
    use DataModel;

    public const string name = 'name';

    #[Describe([Describe::required => true])]
    public SvgName $name;

    public const string classname = 'classname';

    public string $classname = '';
}
