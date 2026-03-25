<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Unit\Embeddings;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Vectora\Pinecone\Core\Http\Json;
use Vectora\Pinecone\Embeddings\OpenAIEmbeddingDriver;

final class OpenAIEmbeddingDriverPayloadTest extends TestCase
{
    public function test_embed_sends_string_input_field(): void
    {
        $body = Json::encode([
            'data' => [['index' => 0, 'embedding' => [0.1]]],
        ]);
        [$client, $mock] = $this->clientWithMock([new Response(200, [], $body)]);
        $f = new HttpFactory;
        $d = new OpenAIEmbeddingDriver($client, $f, $f, 'k', 'text-embedding-3-small');

        $d->embed('hello');

        $req = $mock->getLastRequest();
        $this->assertNotNull($req);
        $raw = (string) $req->getBody();
        $this->assertStringContainsString('"input":"hello"', $raw);
    }

    public function test_embed_many_sends_array_input_field(): void
    {
        $body = Json::encode([
            'data' => [
                ['index' => 0, 'embedding' => [1.0]],
                ['index' => 1, 'embedding' => [2.0]],
            ],
        ]);
        [$client, $mock] = $this->clientWithMock([new Response(200, [], $body)]);
        $f = new HttpFactory;
        $d = new OpenAIEmbeddingDriver($client, $f, $f, 'k', 'm');

        $d->embedMany(['a', 'b']);

        $req = $mock->getLastRequest();
        $this->assertNotNull($req);
        $raw = (string) $req->getBody();
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded['input'] ?? null);
        $this->assertSame(['a', 'b'], $decoded['input']);
    }

    /**
     * @param  list<ResponseInterface>  $responses
     * @return array{0: Client, 1: MockHandler}
     */
    private function clientWithMock(array $responses): array
    {
        $mock = new MockHandler($responses);
        $stack = new HandlerStack($mock);

        return [new Client(['handler' => $stack]), $mock];
    }
}
