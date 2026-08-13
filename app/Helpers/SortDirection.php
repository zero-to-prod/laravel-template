<?php

namespace App\Helpers;

/**
 * Which way an ordered listing runs.
 *
 * The value is both what a link carries and what the database is asked to order
 * by, so it is only ever reached through a case: a direction taken straight off a
 * request reaches the query unchecked, and anything unrecognised has to collapse
 * onto a case before it gets there. A heading already ordered links to the
 * opposite, so the pair is closed — a third case would have no opposite to offer
 * and no indicator to draw, and both are answered by exhaustive matching, which
 * fails loudly until one is given.
 */
enum SortDirection: string
{
    case asc = 'asc';
    case desc = 'desc';

    public function opposite(): self
    {
        return match ($this) {
            self::asc => self::desc,
            self::desc => self::asc,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::asc => 'chevron-up',
            self::desc => 'chevron-down',
        };
    }

    public function aria(): string
    {
        return match ($this) {
            self::asc => 'ascending',
            self::desc => 'descending',
        };
    }
}
