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
        Table::name => 'personal_access_tokens',
        Table::collate => Collation::utf8mb4_unicode_ci->value,
        Table::indexes => [
            'personal_access_tokens_tokenable_id_tokenable_type_index' => [
                self::tokenable_id,
                self::tokenable_type,
            ],
        ],
    ])]
enum PersonalAccessTokens: string
{
    use HasColumnAttribute;

    #[Column([
        Column::name => self::id,
        Column::comment => 'The unique identifier of the token',
        Column::type => ColumnType::bigint->value,
        Column::nullable => false,
        Column::primary_key => true,
        Column::auto_increment => true,
    ])]
    case id = 'id';

    #[Column([
        Column::name => self::tokenable_type,
        Column::comment => 'The class of the model the token belongs to',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case tokenable_type = 'tokenable_type';

    #[Column([
        Column::name => self::tokenable_id,
        Column::comment => 'The identifier of the model the token belongs to',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case tokenable_id = 'tokenable_id';

    #[Column([
        Column::name => self::name,
        Column::comment => 'The name the token was issued under',
        Column::type => ColumnType::varchar->value,
        Column::length => 255,
        Column::nullable => false,
    ])]
    case name = 'name';

    #[Column([
        Column::name => self::token,
        Column::comment => 'The hashed token',
        Column::type => ColumnType::varchar->value,
        Column::length => 64,
        Column::nullable => false,
        Column::unique => true,
    ])]
    case token = 'token';

    #[Column([
        Column::name => self::abilities,
        Column::comment => 'The abilities granted to the token',
        Column::type => ColumnType::text->value,
        Column::nullable => true,
    ])]
    case abilities = 'abilities';

    #[Column([
        Column::name => self::last_used_at,
        Column::comment => 'When the token was last used',
        Column::type => ColumnType::timestamp->value,
        Column::nullable => true,
    ])]
    case last_used_at = 'last_used_at';

    #[Column([
        Column::name => self::expires_at,
        Column::comment => 'When the token expires',
        Column::type => ColumnType::timestamp->value,
        Column::nullable => true,
    ])]
    case expires_at = 'expires_at';

    #[Column([
        Column::name => self::created_at,
        Column::comment => 'When the token was created',
        Column::type => ColumnType::timestamp->value,
        Column::nullable => true,
    ])]
    case created_at = 'created_at';

    #[Column([
        Column::name => self::updated_at,
        Column::comment => 'When the token was last updated',
        Column::type => ColumnType::timestamp->value,
        Column::nullable => true,
    ])]
    case updated_at = 'updated_at';
}
