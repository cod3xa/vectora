<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Vectora\Pinecone\Core\Exception\ApiErrorCategory;
use Vectora\Pinecone\Core\Exception\ApiException;

final class ApiExceptionTest extends TestCase
{
    public function test_is_rate_limited(): void
    {
        $e = new ApiException('x', 429);
        $this->assertTrue($e->isRateLimited());
        $this->assertFalse((new ApiException('y', 400))->isRateLimited());
    }

    public function test_category_matches_status(): void
    {
        $this->assertSame(ApiErrorCategory::NotFound, (new ApiException('n', 404))->category());
        $this->assertSame(ApiErrorCategory::Server, (new ApiException('s', 503))->category());
    }

    public function test_authentication_helpers(): void
    {
        $this->assertTrue((new ApiException('a', 401))->isAuthenticationError());
        $this->assertTrue((new ApiException('b', 403))->isAuthenticationError());
        $this->assertFalse((new ApiException('c', 404))->isAuthenticationError());
    }

    public function test_not_found_helper(): void
    {
        $this->assertTrue((new ApiException('n', 404))->isNotFound());
        $this->assertFalse((new ApiException('x', 400))->isNotFound());
    }

    public function test_client_and_server_helpers(): void
    {
        $this->assertTrue((new ApiException('c', 418))->isClientError());
        $this->assertFalse((new ApiException('s', 500))->isClientError());

        $this->assertTrue((new ApiException('s', 500))->isServerError());
        $this->assertFalse((new ApiException('c', 400))->isServerError());
    }
}
