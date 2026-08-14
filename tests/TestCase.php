<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tests\Support\OasDocument;
use ZeroToProd\LaravelOpenapi\ValidatesSchema;
use ZeroToProd\SchemaValidator\SchemaValidator;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;
    use ValidatesSchema {
        assertMatchesSchema as private assertLeagueMatchesSchema;
    }

    private static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'www.gravatar.com/avatar/*' => Http::response('gravatar', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        if (! self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }
    }

    /**
     * Asserts the response matches the document under both validators that
     * enforce it: league, and the Laravel rules requests are validated by.
     *
     * The two read one document in two vocabularies, so the same `format`,
     * `maxLength` or `nullable` can mean one thing on the way out and another on
     * the way in. Wrapping the assertion is what puts every response body in
     * front of both, and a new endpoint cannot forget to.
     *
     * @template TResponse of SymfonyResponse
     *
     * @param  TestResponse<TResponse>  $TestResponse
     * @return TestResponse<TResponse>
     */
    protected function assertMatchesSchema(TestResponse $TestResponse): TestResponse
    {
        $this->assertLeagueMatchesSchema($TestResponse);

        $Request = $TestResponse->baseRequest;

        if ($Request === null) {
            Assert::fail('The response was not produced by an HTTP test request, so no operation can be resolved.');
        }

        // The document keys its paths by the route uri, so the matched route is
        // what names the operation, templated parameters and all.
        $path = '/'.ltrim($Request->route()->uri(), '/');
        $method = strtolower($Request->getMethod());
        $status = (string) $TestResponse->baseResponse->getStatusCode();
        $operation = strtoupper($method).' '.$path.' '.$status;

        // league validated the response, so the operation is declared. Saying so
        // here is what stops a missed lookup from skipping the cross check in
        // silence, which is the failure the cross check exists to catch.
        Assert::assertContains($status, OasDocument::generated()->statuses($path, $method), sprintf(
            'The document declares no %s response for %s %s, so nothing cross checked it.',
            $status,
            strtoupper($method),
            $path,
        ));

        $schema = OasDocument::generated()->responseSchema($path, $method, $status);

        if ($schema !== null) {
            $this->assertBodyMatchesRules($schema, $this->body($TestResponse), $operation);
        }

        return $TestResponse;
    }

    /**
     * Asserts the body the document describes is one the request validator's
     * rules also admit.
     *
     * This is the half a wrong mapping slips through: league accepting a value
     * says nothing about the rules the same schema translates to, and only
     * running both over the same bytes tells them apart.
     *
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $body
     */
    protected function assertBodyMatchesRules(array $schema, array $body, string $operation): void
    {
        $Validator = SchemaValidator::make($body, $schema);

        if ($Validator->fails()) {
            Assert::fail(sprintf(
                "%s: the document admits this body under league and refuses it under the request validator's rules:\n  - %s",
                $operation,
                implode("\n  - ", $Validator->errors()->all()),
            ));
        }

        $this->addToAssertionCount(1);
    }

    /**
     * @template TResponse of SymfonyResponse
     *
     * @param  TestResponse<TResponse>  $TestResponse
     * @return array<string, mixed>
     */
    private function body(TestResponse $TestResponse): array
    {
        $body = $TestResponse->json();

        /** @var array<string, mixed> $body */
        $body = is_array($body) ? $body : [];

        return $body;
    }
}
