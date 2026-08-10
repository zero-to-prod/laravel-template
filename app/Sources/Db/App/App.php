<?php

namespace App\Sources\Db\App;

use App\Sources\Db\Support\Collation;
use App\Sources\Db\Support\Schema;

#[Schema([
    Schema::name => 'app',
    Schema::collate => Collation::utf8mb4_0900_ai_ci->value,
])]
enum App: string {}
