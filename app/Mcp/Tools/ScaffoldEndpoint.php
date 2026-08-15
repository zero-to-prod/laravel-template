<?php

namespace App\Mcp\Tools;

use App\Mcp\Endpoint\EndpointApi;
use App\Mcp\Endpoint\EndpointBlueprint;
use App\Mcp\Endpoint\EndpointParameter;
use App\Mcp\Endpoint\EndpointRenderer;
use App\Mcp\Endpoint\EndpointWriter;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Override;

#[IsIdempotent]
class ScaffoldEndpoint extends Tool
{
    protected string $name = 'scaffold-endpoint';
    protected string $description = <<<'MARKDOWN'
        Writes the artifacts of one API endpoint module:
        the route case, the request DTO, the response DTO, the schema, the controller and the test.

        What the convention derives is written finished. What needs a decision — the behaviour of the
        action, the values a test asserts, the state an error status is reached from — is written as a
        a pending-work marker, which `composer check` reports until it is resolved.

        Declare only the statuses you will also write a test for: a declared response no test reaches
        fails `openapi:coverage`, while one that is never reached and never declared is silent.

        A templated path such as /api/posts/{post} needs one path_parameters entry per templated
        segment. The parameter class is shared by every operation keyed on that path, so it is written
        beside the modules rather than inside one of them, and one already there is referenced as it is.
        MARKDOWN;

    /** @return array<string, mixed> */
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'api' => $schema->string()
                ->enum(array_column(EndpointApi::cases(), 'value'))
                ->description('The API to extend. Defaults to public.'),
            'module' => $schema->string()
                ->description('Directory under app/Modules/Api, such as Widget/Part/Store.')
                ->required(),
            'class_prefix' => $schema->string()
                ->description('Prefix the generated classes share, such as WidgetPartStore. Defaults to the module path with the separators removed.'),
            'method' => $schema->string()
                ->enum(['get', 'post', 'put', 'patch', 'delete'])
                ->description('The HTTP method this operation serves.')
                ->required(),
            'path' => $schema->string()
                ->description('The full path under the selected API prefix (/api for public or /admin/api for admin).')
                ->required(),
            'path_parameters' => $schema->array()
                ->items($schema->object([
                    'name' => $schema->string()->description('The templated segment, without the braces.')->required(),
                    'description' => $schema->string()->description('What the segment identifies.')->required(),
                    'class' => $schema->string()->description('Fully qualified name of a parameter class that is already there, to reference rather than write.'),
                ]))
                ->description('One entry per templated segment of the path. Each is declared as a string: a path segment arrives as text.'),
            'route_case' => $schema->string()
                ->description('Name of the ApiRoute case. Defaults to the path after /api with the separators as underscores. An existing case for the same path is reused.'),
            'authenticated' => $schema->boolean()
                ->description('Register behind auth:sanctum in routes/api_auth.php rather than routes/api.php. Decides whether the 401 is the middleware message or the envelope. Defaults to true.'),
            'security' => $schema->boolean()
                ->description('Declare the bearer security requirement. Defaults to the value of authenticated. Set it on a public route that reads the token itself.'),
            'success_status' => $schema->integer()
                ->enum([200, 201])
                ->description('The status the action returns when it succeeds. Defaults to 200.'),
            'operation_id' => $schema->string()->description('The operationId of this operation.')->required(),
            'summary' => $schema->string()->description('One sentence saying what the operation does.')->required(),
            'tags' => $schema->array()
                ->items($schema->string())
                ->description('The tags the operation is grouped under, such as ["Tokens"].')
                ->required(),
            'success_description' => $schema->string()
                ->description('What the success response carries.')
                ->required(),
            'request_fields' => $schema->array()
                ->items($schema->object([
                    'name' => $schema->string()->description('The field name, used for the constant and the property.')->required(),
                    'type' => $schema->string()->enum(['string', 'int', 'float', 'bool', 'array'])->description('The PHP type. Defaults to string.'),
                    'nullable' => $schema->boolean()->description('Whether the value may be null.'),
                    'required' => $schema->boolean()->description('Whether the key must be present.'),
                    'table' => $schema->string()->description('Short name of the column enum in App\Sources\Db\App, such as Users. The field adopts that column schema.'),
                    'column' => $schema->string()->description('The column case, when it differs from the field name.'),
                    'description' => $schema->string()->description('Added to the column schema, or used alone when no column backs the field.'),
                ]))
                ->description('The body fields. Omit for an endpoint that takes no body: the request DTO and the 422 are written only when there are fields.'),
            'response_fields' => $schema->array()
                ->items($schema->object([
                    'name' => $schema->string()->description('The field name, used for the constant and the property.')->required(),
                    'type' => $schema->string()->enum(['string', 'int', 'float', 'bool', 'array'])->description('The PHP type. Defaults to string.'),
                    'nullable' => $schema->boolean()->description('Whether the value may be null. The PHP type decides nullability, not the column.'),
                    'table' => $schema->string()->description('Short name of the column enum in App\Sources\Db\App, such as Users.'),
                    'column' => $schema->string()->description('The column case, when it differs from the field name.'),
                    'description' => $schema->string()->description('Used when no column backs the field.'),
                    'items_of' => $schema->string()->description('For an array field: the fully qualified response class whose data() describes one item, so the list and the single object cannot drift.'),
                ]))
                ->description('The fields of the data key. Omit for a response that publishes no data.'),
            'paginated' => $schema->boolean()
                ->description('For an index: declare the page and per_page query parameters and publish a pagination object beside the items. The items field still needs items_of.'),
            'error_statuses' => $schema->array()
                ->items($schema->object([
                    'status' => $schema->integer()->enum([400, 403, 404, 409, 415])->description('The status.')->required(),
                    'description' => $schema->string()->description('What the status means for this endpoint.')->required(),
                ]))
                ->description('Statuses beyond the success, the 401 and the 422, each written with a test to finish. Every one declared must be reached by a test.'),
            'dry_run' => $schema->boolean()->description('Return what would be written without writing it.'),
            'force' => $schema->boolean()->description('Overwrite files that are already there.'),
        ];
    }

    public function handle(Request $Request): Response
    {
        return $this->scaffold($Request->validate($this->rules()));
    }

    /** @param array<string, mixed> $input */
    public function scaffold(array $input): Response
    {

        $Blueprint = EndpointBlueprint::from($input);

        if (! str_starts_with($Blueprint->path, $Blueprint->api->prefix().'/')) {
            return Response::error(sprintf(
                'The %s API path must start with %s/.',
                $Blueprint->api->value,
                $Blueprint->api->prefix(),
            ));
        }
        $mismatch = $this->mismatchedParameters($Blueprint);

        if ($mismatch !== null) {
            return Response::error($mismatch);
        }

        $endpointWriter = new EndpointWriter($Blueprint, new EndpointRenderer($Blueprint));

        $files = [...$endpointWriter->files(), ...$endpointWriter->parameters()];
        $edits = $endpointWriter->edits();

        if (($input['dry_run'] ?? false) === true) {
            return Response::text($this->preview($files, $edits));
        }

        $collisions = $endpointWriter->collisions();

        if ($collisions !== [] && ($input['force'] ?? false) !== true) {
            return Response::error(sprintf(
                "These files are already there, so nothing was written:\n%s\nPass force to overwrite them.",
                $this->listed($collisions),
            ));
        }

        $endpointWriter->write();

        return Response::text($this->report($Blueprint, array_keys($files), array_keys($edits)));
    }

    /**
     * Every templated segment needs one parameter, and nothing else does: an
     * undeclared segment leaves the operation without the parameter the
     * document requires, and a spare one describes a segment that is not there.
     */
    private function mismatchedParameters(EndpointBlueprint $EndpointBlueprint): ?string
    {
        $templated = $EndpointBlueprint->templatedSegments();
        $declared = array_map(
            static fn (EndpointParameter $EndpointParameter): string => $EndpointParameter->name,
            $EndpointBlueprint->pathParameters,
        );

        sort($templated);
        sort($declared);

        if ($templated === $declared) {
            return null;
        }

        return sprintf(
            'The path templates [%s], while path_parameters declares [%s]. Every templated segment needs one entry, and nothing else does.',
            implode(', ', $templated),
            implode(', ', $declared),
        );
    }

    /** @return array<string, list<string>> */
    private function rules(): array
    {
        return [
            'module' => ['required', 'string', 'regex:/^[A-Za-z][A-Za-z0-9]*(\/[A-Za-z][A-Za-z0-9]*)*$/'],
            'class_prefix' => ['nullable', 'string', 'regex:/^[A-Z][A-Za-z0-9]*$/'],
            'method' => ['required', 'string', 'in:get,post,put,patch,delete'],
            'api' => ['nullable', 'string', 'in:public,admin'],
            'path' => ['required', 'string'],
            'path_parameters' => ['array'],
            'path_parameters.*.name' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]*$/'],
            'path_parameters.*.description' => ['required', 'string'],
            'path_parameters.*.class' => ['nullable', 'string'],
            'route_case' => ['nullable', 'string', 'regex:/^[a-z][a-z0-9_]*$/'],
            'authenticated' => ['boolean'],
            'security' => ['boolean'],
            'success_status' => ['integer', 'in:200,201'],
            'operation_id' => ['required', 'string'],
            'summary' => ['required', 'string'],
            'tags' => ['required', 'array', 'min:1'],
            'tags.*' => ['required', 'string'],
            'success_description' => ['required', 'string'],
            'request_fields' => ['array'],
            'request_fields.*.name' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]*$/'],
            'request_fields.*.type' => ['nullable', 'string', 'in:string,int,float,bool,array'],
            'request_fields.*.nullable' => ['boolean'],
            'request_fields.*.required' => ['boolean'],
            'request_fields.*.table' => ['nullable', 'string', 'regex:/^[A-Z][A-Za-z0-9]*$/'],
            'request_fields.*.column' => ['nullable', 'string', 'regex:/^[a-z][a-z0-9_]*$/'],
            'request_fields.*.description' => ['nullable', 'string'],
            'response_fields' => ['array'],
            'response_fields.*.name' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]*$/'],
            'response_fields.*.type' => ['nullable', 'string', 'in:string,int,float,bool,array'],
            'response_fields.*.nullable' => ['boolean'],
            'response_fields.*.table' => ['nullable', 'string', 'regex:/^[A-Z][A-Za-z0-9]*$/'],
            'response_fields.*.column' => ['nullable', 'string', 'regex:/^[a-z][a-z0-9_]*$/'],
            'response_fields.*.description' => ['nullable', 'string'],
            'response_fields.*.items_of' => ['nullable', 'string'],
            'paginated' => ['boolean'],
            'error_statuses' => ['array'],
            'error_statuses.*.status' => ['required', 'integer', 'in:400,403,404,409,415'],
            'error_statuses.*.description' => ['required', 'string'],
            'dry_run' => ['boolean'],
            'force' => ['boolean'],
        ];
    }

    /**
     * @param  array<string, string>  $files
     * @param  array<string, string>  $edits
     */
    private function preview(array $files, array $edits): string
    {
        $sections = ['Nothing was written. This is what would be.'];

        foreach ([...$files, ...$edits] as $path => $contents) {
            $sections[] = sprintf("### %s\n\n```php\n%s```", $path, $contents);
        }

        return implode("\n\n", $sections)."\n";
    }

    /**
     * @param  list<string>  $files
     * @param  list<string>  $edits
     */
    private function report(EndpointBlueprint $EndpointBlueprint, array $files, array $edits): string
    {
        $remaining = [
            sprintf('%s::__invoke() returns an empty payload. Write what the action does.', $EndpointBlueprint->controllerClass()),
            'The happy path test asserts shape only. Assert the values the endpoint returns.',
        ];

        if ($EndpointBlueprint->hasBody() && $EndpointBlueprint->blankableField() === null) {
            $remaining[] = 'The 422 test needs a body the document rejects, and cannot use assertMatchesSchema.';
        }

        foreach ($EndpointBlueprint->errorStatuses as $status) {
            $remaining[] = sprintf('The %d test needs the state that status is reached from.', $status['status']);
        }

        return sprintf(
            "Wrote:\n%s\nEdited:\n%s\nStill yours:\n%s\nEach one is a `@todo` in the file. ForbidTodoAnnotationRector reports them, so `composer check` fails until they are gone.\nRun `sail composer fix`, then `sail composer check`.\n",
            $this->listed($files),
            $this->listed($edits),
            $this->listed($remaining),
        );
    }

    /** @param  list<string>  $items */
    private function listed(array $items): string
    {
        return implode('', array_map(static fn (string $item): string => '  - '.$item."\n", $items));
    }
}
