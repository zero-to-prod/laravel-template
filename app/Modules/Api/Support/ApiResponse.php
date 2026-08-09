<?php

namespace App\Modules\Api\Support;

use App\Helpers\DataModel;
use Illuminate\Contracts\Support\MessageProvider;
use ReflectionException;
use Zerotoprod\DataModel\Describe;

readonly class ApiResponse
{
    use DataModel;

    /** @link $success */
    public const string success = 'success';

    #[Describe(['default' => true])]
    public bool $success;

    /** @link $message */
    public const string message = 'message';

    #[Describe(['nullable'])]
    public ?string $message;

    /** @link $errors */
    public const string errors = 'errors';

    /** @var array<array-key, mixed> */
    #[Describe(['default' => []])]
    public array $errors;

    /** @link $data */
    public const string data = 'data';

    #[Describe(['default' => []])]
    public mixed $data;

    /** @link $type */
    public const string type = 'type';

    public string $type;

    /** @throws ReflectionException */
    public static function ok(string $type, mixed $data = null, ?string $message = null): self
    {
        return self::from([
            self::success => true,
            self::message => $message ?? $type,
            self::data => $data,
            self::type => $type,
        ]);
    }

    /**
     * @param  array<array-key, mixed>|null  $errors
     *
     * @throws ReflectionException
     */
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

    /** @throws ReflectionException */
    public static function fromValidator(MessageProvider $MessageProvider, string $message = 'unprocessable entity', mixed $data = []): self
    {
        return self::error($message, $MessageProvider->getMessageBag()->toArray(), $data);
    }
}
