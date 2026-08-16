<?php

namespace App\View\DataModels;

use App\Helpers\DataModel;
use App\Helpers\HttpVerb;
use App\Modules\Api\Support\AbilityQuery;
use App\Modules\Settings\Credentials\TokenUpdateRequest;
use App\Routes\Auth;
use App\Routes\Web;
use Illuminate\Support\Facades\Config;
use Zerotoprod\DataModel\Describe;

readonly class AbilityTable
{
    use DataModel;

    public const string field = TokenUpdateRequest::abilities.'[]';
    public const string id = 'id';

    #[Describe([Describe::required => true])]
    public string $id;

    public const string name = 'name';

    #[Describe([Describe::required => true])]
    public string $name;

    public const string granted = 'granted';

    /** @var list<string> */
    #[Describe([Describe::default => []])]
    public array $granted;

    /** @return list<HttpVerb> */
    public function verbs(): array
    {
        return HttpVerb::cases();
    }

    /** @return list<AbilityRow> */
    public function rows(): array
    {
        $rows = [];

        foreach (AbilityQuery::get() as $path => $verbs) {
            $rows[] = AbilityRow::from([
                AbilityRow::path => $path,
                AbilityRow::verbs => $verbs,
                AbilityRow::granted => $this->granted,
                AbilityRow::every => $this->every(),
            ]);
        }

        return $rows;
    }

    /** @return array<string, list<AbilityRow>> */
    public function groups(): array
    {
        $groups = [];

        foreach (AbilityQuery::groups() as $name => $endpoints) {
            $groups[$name] = [];

            foreach ($endpoints as $path => $verbs) {
                $groups[$name][] = AbilityRow::from([
                    AbilityRow::path => $path,
                    AbilityRow::verbs => $verbs,
                    AbilityRow::granted => $this->granted,
                    AbilityRow::every => $this->every(),
                ]);
            }
        }

        return $groups;
    }

    public function every(): bool
    {
        return in_array(HttpVerb::every, $this->granted, true);
    }

    /** @return array{base_url: string, openapi_url: string, headers: string, llms_url: string} */
    public function mcpConnection(string $api): array
    {
        return [
            'base_url' => url('/'),
            'openapi_url' => url(Config::string("openapi.schemas.$api.route.uri")),
            'headers' => 'Authorization:Bearer <token>',
            'llms_url' => url(Web::llms->value),
        ];
    }

    public function action(): string
    {
        return Auth::settingsCredential->url([Auth::credentialParameter => $this->id]);
    }
}
