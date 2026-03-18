<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Unit\Core;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Vectora\Pinecone\Core\Http\Json;
use Vectora\Pinecone\Core\Http\PineconeHttpTransport;
use Vectora\Pinecone\Core\Http\RetryPolicy;

final class PineconeHttpTransportRetryTest extends TestCase
{
    public function test_retries_on_503_then_succeeds(): void
    {
        $http = new MockHttpClient;
        $http->responses[] = new Response(503, [], '');
        $http->responses[] = new Response(200, [], '{"ok":true}');
        $f = PineconeTestFactories::httpFactory();
        $t = new PineconeHttpTransport(
            $http,
            $f,
            $f,
            'k',
            '2025-10',
            new RetryPolicy(maxAttempts: 3, initialDelayMs: 1, maxDelayMs: 5)
        );

        $r = $t->postJson('https://h.example', '/p', ['a' => 1]);
        $this->assertSame(200, $r->getStatusCode());
        $this->assertCount(2, $http->requests);
    }

    public function test_respects_retry_after_on_429(): void
    {
        $http = new MockHttpClient;
        $http->responses[] = new Response(429, ['Retry-After' => '0'], '');
        $http->responses[] = new Response(200, [], Json::encode(['x' => 1]));
        $f = PineconeTestFactories::httpFactory();
        $t = new PineconeHttpTransport(
            $http,
            $f,
            $f,
            'k',
            '2025-10',
            new RetryPolicy(maxAttempts: 3, initialDelayMs: 100, respectRetryAfter: true, maxDelayMs: 50)
        );

        $r = $t->postJson('https://h.example', '/p', []);
        $this->assertSame(200, $r->getStatusCode());
        $this->assertCount(2, $http->requests);
    }
}
