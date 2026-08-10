<?php

declare(strict_types=1);

namespace App\Sources\Db\App;

use ZeroToProd\DbModel\Schema;

#[Schema([
    Schema::name => 'app',
    Schema::collate => 'utf8mb4_0900_ai_ci',
])]
enum App: string {}
