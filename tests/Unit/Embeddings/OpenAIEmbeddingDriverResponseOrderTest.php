<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Unit\Embeddings;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Vectora\Pinecone\Core\Http\Json;
use Vectora\Pinecone\Embeddings\OpenAIEmbeddingDriver;

final class OpenAIEmbeddingDriverResponseOrderTest extends TestCase
{
    public function test_reorders_openai_data_by_index(): void
    {
        $body = Json::encode([
            'data' => [
                ['index' => 1, 'embedding' => [2.0]],
                ['index' => 0, 'embedding' => [1.0]],
            ],
        ]);
        $mock = new MockHandler([new Response(200, [], $body)]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $f = new HttpFactory;
        $d = new OpenAIEmbeddingDriver($client, $f, $f, 'k', 'm');

        $out = $d->embedMany(['first', 'second']);

        $this->assertSame([[1.0], [2.0]], $out);
    }
}
