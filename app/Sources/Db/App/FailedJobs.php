<?php

declare(strict_types=1);

namespace App\Sources\Db\App;

use App\Sources\Db\HasColumn;
use ZeroToProd\DbModel\Column;
use ZeroToProd\DbModel\ColumnType;
use ZeroToProd\DbModel\Table;

/**
 * @method string type()
 * @method string|null comment()
 * @method int|null length()
 * @method bool|null nullable()
 * @method bool|null unique()
 * @method bool|null primary_key()
 * @method bool|null auto_increment()
 */
#[Table(
    schema: App::class,
    attributes: [
        Table::name => 'failed_jobs',
        Table::collate => 'utf8mb4_unicode_ci',
    ])]
enum FailedJobs: string
{
    use HasColumn;

    #[Column([
        Column::name => self::id,
        Column::comment => 'The unique identifier of the failure',
        Column::type => ColumnType::bigint->value,
        Column::nullable => false,
        Column::primary_key => true,
        Column::auto_increment => true,
    ])]
    case id = 'id';

    #[Column([
        Column::name => self::uuid,
        Column::comment => 'The unique identifier of the job that failed',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
        Column::unique => true,
    ])]
    case uuid = 'uuid';

    #[Column([
        Column::name => self::connection,
        Column::comment => 'The queue connection the job ran on',
        Column::type => ColumnType::text->value,
        Column::nullable => false,
    ])]
    case connection = 'connection';

    #[Column([
        Column::name => self::queue,
        Column::comment => 'The queue the job ran on',
        Column::type => ColumnType::text->value,
        Column::nullable => false,
    ])]
    case queue = 'queue';

    #[Column([
        Column::name => self::payload,
        Column::comment => 'The serialized job',
        Column::type => ColumnType::longtext->value,
        Column::nullable => false,
    ])]
    case payload = 'payload';

    #[Column([
        Column::name => self::exception,
        Column::comment => 'The exception that failed the job',
        Column::type => ColumnType::longtext->value,
        Column::nullable => false,
    ])]
    case exception = 'exception';

    #[Column([
        Column::name => self::failed_at,
        Column::comment => 'When the job failed',
        Column::type => ColumnType::timestamp->value,
        Column::nullable => false,
    ])]
    case failed_at = 'failed_at';
}
