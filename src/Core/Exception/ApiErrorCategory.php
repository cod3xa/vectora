<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Core\Exception;

/** High-level grouping for Pinecone HTTP failures (after retries). */
enum ApiErrorCategory: string
{
    case RateLimited = 'rate_limited';
    case Authentication = 'authentication';
    case NotFound = 'not_found';
    case BadRequest = 'bad_request';
    case Client = 'client';
    case Server = 'server';
    case Unknown = 'unknown';

    public static function fromStatusCode(int $code): self
    {
        if ($code === 429) {
            return self::RateLimited;
        }
        if ($code === 401 || $code === 403) {
            return self::Authentication;
        }
        if ($code === 404) {
            return self::NotFound;
        }
        if ($code === 400 || $code === 422) {
            return self::BadRequest;
        }
        if ($code >= 400 && $code <= 499) {
            return self::Client;
        }
        if ($code >= 500 && $code <= 599) {
            return self::Server;
        }

        return self::Unknown;
    }
}
