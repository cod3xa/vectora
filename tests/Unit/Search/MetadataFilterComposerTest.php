<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Unit\Search;

use PHPUnit\Framework\TestCase;
use Vectora\Pinecone\Search\MetadataFilterComposer;

final class MetadataFilterComposerTest extends TestCase
{
    public function test_eq_structure(): void
    {
        $f = MetadataFilterComposer::eq('lang', 'en');
        $this->assertSame(['$eq' => 'en'], $f['lang']);
    }
}
