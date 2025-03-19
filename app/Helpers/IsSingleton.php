<?php

namespace App\Helpers;

trait IsSingleton
{
    private static self $instance;

    public static function getInstance(): self
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }

        self::$instance = new self;
        return self::$instance;
    }
}