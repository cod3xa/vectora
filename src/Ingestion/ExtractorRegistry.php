<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Ingestion;

use Vectora\Pinecone\Contracts\TextExtractor;
use Vectora\Pinecone\Ingestion\Extractors\DocxTextExtractor;
use Vectora\Pinecone\Ingestion\Extractors\HtmlTextExtractor;
use Vectora\Pinecone\Ingestion\Extractors\PdfTextExtractor;
use Vectora\Pinecone\Ingestion\Extractors\PlainTextExtractor;

/**
 * Picks the first {@see TextExtractor} that {@see TextExtractor::supports()} the path.
 */
final class ExtractorRegistry
{
    /** @var list<TextExtractor> */
    private array $extractors;

    public function __construct(
        ?PlainTextExtractor $plain = null,
        ?HtmlTextExtractor $html = null,
        ?DocxTextExtractor $docx = null,
        ?PdfTextExtractor $pdf = null,
    ) {
        $this->extractors = [
            $plain ?? new PlainTextExtractor,
            $html ?? new HtmlTextExtractor,
            $docx ?? new DocxTextExtractor,
            $pdf ?? new PdfTextExtractor,
        ];
    }

    public function extractFromPath(string $path): string
    {
        foreach ($this->extractors as $ex) {
            if ($ex->supports($path)) {
                return $ex->extract($path);
            }
        }

        throw new \InvalidArgumentException(sprintf('No extractor registered for path [%s].', $path));
    }

    public function register(TextExtractor $extractor): void
    {
        array_unshift($this->extractors, $extractor);
    }
}
