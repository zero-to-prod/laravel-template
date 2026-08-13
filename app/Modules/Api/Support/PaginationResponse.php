<?php

namespace App\Modules\Api\Support;

use App\Helpers\DataModel;
use Illuminate\Pagination\LengthAwarePaginator;

readonly class PaginationResponse
{
    use DataModel;
    use HasResponseSchema;

    public const string page = 'page';

    #[Response([Response::description => 'The page this body carries, counting from 1.'])]
    public int $page;

    public const string per_page = 'per_page';

    #[Response([Response::description => 'How many entries a full page carries.'])]
    public int $per_page;

    public const string total = 'total';

    #[Response([Response::description => 'How many entries there are across every page.'])]
    public int $total;

    public const string last_page = 'last_page';

    #[Response([Response::description => 'The highest page that carries anything. 1 when there is nothing at all.'])]
    public int $last_page;

    /** @param  LengthAwarePaginator<int, mixed>  $LengthAwarePaginator */
    public static function of(LengthAwarePaginator $LengthAwarePaginator): self
    {
        return self::from([
            self::page => $LengthAwarePaginator->currentPage(),
            self::per_page => $LengthAwarePaginator->perPage(),
            self::total => $LengthAwarePaginator->total(),
            self::last_page => $LengthAwarePaginator->lastPage(),
        ]);
    }
}
