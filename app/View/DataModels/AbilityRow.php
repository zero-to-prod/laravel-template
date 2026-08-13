<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Helpers\HttpVerb;
use Zerotoprod\DataModel\Describe;

readonly class AbilityRow
{
    use DataModel;

    public const string path = 'path';

    #[Describe([Describe::required => true])]
    public string $path;

    public const string verbs = 'verbs';

    /** @var list<HttpVerb> */
    #[Describe([Describe::required => true])]
    public array $verbs;

    public const string granted = 'granted';

    /** @var list<string> */
    #[Describe([Describe::default => []])]
    public array $granted;

    public const string every = 'every';

    #[Describe([Describe::default => false])]
    public bool $every;

    public function ability(HttpVerb $HttpVerb): string
    {
        return $HttpVerb->ability($this->path);
    }

    public function bound(HttpVerb $HttpVerb): bool
    {
        return in_array($HttpVerb, $this->verbs, true);
    }

    public function checked(HttpVerb $HttpVerb): bool
    {
        return $this->every || in_array($this->ability($HttpVerb), $this->granted, true);
    }
}
