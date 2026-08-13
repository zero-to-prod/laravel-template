<?php

namespace App\Modules\Api\Support;

use Illuminate\Database\Eloquent\Model;

readonly class Unique implements ValueCheck
{
    /**
     * The model rather than the table name: the table a check runs against is
     * one the application has a model for, and the model already knows its name.
     *
     * @param  class-string<Model>  $model
     */
    public function __construct(private string $model, private string $column, private ?string $message = null) {}

    /** @param  array<string, mixed>  $data */
    public function violation(mixed $value, string $path, array $data): ?Violation
    {
        $exists = $this->model::query()->where($this->column, $value)->exists();

        return $exists
            ? new Violation($path, 'unique', $this->message ?? "That $this->column is already taken.")
            : null;
    }
}
