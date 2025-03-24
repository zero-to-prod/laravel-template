<?php

namespace App\Modules\Api;

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
    public ResponseType $type;

    public static function ok(ResponseType $ResponseType, mixed $data = null, ?string $message = null): self
    {
        return self::from([
            self::success => true,
            self::message => $message ?? $ResponseType->value,
            self::data => $data,
            self::type => $ResponseType,
        ]);
    }

    public static function error(string $message, ?array $errors = null): self
    {
        return self::from([
            self::success => false,
            self::message => $message,
            self::errors => $errors,
            self::type => ResponseType::error,
        ]);
    }

    public static function fromValidator(Validator $Validator, string $message = 'unprocessable entity'): self
    {
        return self::error($message, $Validator->errors()->toArray());
    }
}