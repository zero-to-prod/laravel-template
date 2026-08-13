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
        Table::name => 'job_batches',
        Table::collate => 'utf8mb4_unicode_ci',
    ])]
enum JobBatches: string
{
    use HasColumn;

    #[Column([
        Column::name => self::id,
        Column::comment => 'The unique identifier of the batch',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
        Column::primary_key => true,
    ])]
    case id = 'id';

    #[Column([
        Column::name => self::name,
        Column::comment => 'The name of the batch',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case name = 'name';

    #[Column([
        Column::name => self::total_jobs,
        Column::comment => 'How many jobs the batch started with',
        Column::type => ColumnType::int->value,
        Column::nullable => false,
    ])]
    case total_jobs = 'total_jobs';

    #[Column([
        Column::name => self::pending_jobs,
        Column::comment => 'How many jobs in the batch have yet to finish',
        Column::type => ColumnType::int->value,
        Column::nullable => false,
    ])]
    case pending_jobs = 'pending_jobs';

    #[Column([
        Column::name => self::failed_jobs,
        Column::comment => 'How many jobs in the batch failed',
        Column::type => ColumnType::int->value,
        Column::nullable => false,
    ])]
    case failed_jobs = 'failed_jobs';

    #[Column([
        Column::name => self::failed_job_ids,
        Column::comment => 'The identifiers of the jobs that failed',
        Column::type => ColumnType::longtext->value,
        Column::nullable => false,
    ])]
    case failed_job_ids = 'failed_job_ids';

    #[Column([
        Column::name => self::options,
        Column::comment => 'The serialized batch options',
        Column::type => ColumnType::mediumtext->value,
        Column::nullable => true,
    ])]
    case options = 'options';

    #[Column([
        Column::name => self::cancelled_at,
        Column::comment => 'The unix timestamp the batch was cancelled',
        Column::type => ColumnType::int->value,
        Column::nullable => true,
    ])]
    case cancelled_at = 'cancelled_at';

    #[Column([
        Column::name => self::created_at,
        Column::comment => 'The unix timestamp the batch was created',
        Column::type => ColumnType::int->value,
        Column::nullable => false,
    ])]
    case created_at = 'created_at';

    #[Column([
        Column::name => self::finished_at,
        Column::comment => 'The unix timestamp the batch finished',
        Column::type => ColumnType::int->value,
        Column::nullable => true,
    ])]
    case finished_at = 'finished_at';
}
