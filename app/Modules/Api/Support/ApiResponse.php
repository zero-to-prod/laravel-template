<?php

namespace App\Modules\Api\Support;

use App\Helpers\DataModel;
use Illuminate\Validation\Validator;
use Zerotoprod\DataModel\Describe;

readonly class ApiResponse
{
    use DataModel;

    /** @link $success */
    public const success = 'success';

    #[Describe(['default' => true])]
    public bool $success;

    /** @link $message */
    public const message = 'message';

    #[Describe(['nullable'])]
    public ?string $message;

    /** @link $errors */
    public const errors = 'errors';

    #[Describe(['default' => []])]
    public array $errors;

    /** @link $data */
    public const data = 'data';

    #[Describe(['default' => []])]
    public mixed $data;

    public const type = 'type';

    public string $type;

    public static function ok(string $type, mixed $data = null, ?string $message = null): self
    {
        return self::from([
            self::success => true,
            self::message => $message ?? $type,
            self::data => $data,
            self::type => $type,
        ]);
    }

    public static function error(string $message, ?array $errors = null, mixed $data = []): self
    {
        return self::from([
            self::success => false,
            self::message => $message,
            self::errors => $errors,
            self::data => $data,
            self::type => 'error',
        ]);
    }

    public static function fromValidator(Validator $Validator, string $message = 'unprocessable entity'): self
    {
        return self::error($message, $Validator->errors()->toArray());
    }
}
