<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Core\Exception;

/** Non-retryable or final API error after retries. */
class ApiException extends PineconeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly ?string $responseBody = null,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function isRateLimited(): bool
    {
        return $this->statusCode === 429;
    }
}
