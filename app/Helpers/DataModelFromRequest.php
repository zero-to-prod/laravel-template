<?php

namespace App\Helpers;

trait DataModelFromRequest
{
    use DataModel;

    public function __construct()
    {
        $request = request()->all();

        if (!$request) {
            return;
        }

        self::from($request, $this);
    }
}