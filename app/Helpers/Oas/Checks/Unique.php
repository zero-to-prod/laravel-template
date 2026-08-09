<?php

namespace App\Helpers\Oas\Checks;

use App\Helpers\Oas\ValueCheck;
use App\Helpers\Oas\Violation;
use Illuminate\Support\Facades\DB;

readonly class Unique implements ValueCheck
{
    public function __construct(
        private string $table,
        private string $column,
        private ?string $message = null,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function violation(mixed $value, string $path, array $data): ?Violation
    {
        $exists = DB::table($this->table)->where($this->column, $value)->exists();

        return $exists
            ? new Violation($path, 'unique', $this->message ?? "That $this->column is already taken.")
            : null;
    }
}
