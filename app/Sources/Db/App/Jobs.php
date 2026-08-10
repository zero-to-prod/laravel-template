<?php

namespace App\Sources\Db\App;

use App\Sources\Db\Support\Collation;
use App\Sources\Db\Support\Column;
use App\Sources\Db\Support\ColumnType;
use App\Sources\Db\Support\HasColumnAttribute;
use App\Sources\Db\Support\Table;

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
        Table::name => 'jobs',
        Table::collate => Collation::utf8mb4_unicode_ci->value,
        Table::indexes => [
            'jobs_queue_index' => [
                self::queue,
            ],
        ],
    ])]
enum Jobs: string
{
    use HasColumnAttribute;

    #[Column([
        Column::name => self::id,
        Column::comment => 'The unique identifier of the queued job',
        Column::type => ColumnType::bigint->value,
        Column::nullable => false,
        Column::primary_key => true,
        Column::auto_increment => true,
    ])]
    case id = 'id';

    #[Column([
        Column::name => self::queue,
        Column::comment => 'The queue the job was pushed onto',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
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
        Column::name => self::attempts,
        Column::comment => 'How many times the job has been attempted',
        Column::type => ColumnType::tinyint->value,
        Column::nullable => false,
    ])]
    case attempts = 'attempts';

    #[Column([
        Column::name => self::reserved_at,
        Column::comment => 'The unix timestamp a worker reserved the job',
        Column::type => ColumnType::int->value,
        Column::nullable => true,
    ])]
    case reserved_at = 'reserved_at';

    #[Column([
        Column::name => self::available_at,
        Column::comment => 'The unix timestamp the job becomes available to run',
        Column::type => ColumnType::int->value,
        Column::nullable => false,
    ])]
    case available_at = 'available_at';

    #[Column([
        Column::name => self::created_at,
        Column::comment => 'The unix timestamp the job was pushed',
        Column::type => ColumnType::int->value,
        Column::nullable => false,
    ])]
    case created_at = 'created_at';
}
