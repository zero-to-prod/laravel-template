<?php

namespace App\Sources\Db\Support;

enum Collation: string
{
    case utf8mb4_0900_ai_ci = 'utf8mb4_0900_ai_ci';
    case utf8mb4_unicode_ci = 'utf8mb4_unicode_ci';
}
