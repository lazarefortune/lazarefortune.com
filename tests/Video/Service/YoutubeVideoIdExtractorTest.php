<?php

declare(strict_types=1);

namespace App\Tests\Video\Service;

use App\Video\Service\YoutubeVideoIdExtractor;
use PHPUnit\Framework\TestCase;

final class YoutubeVideoIdExtractorTest extends TestCase
{
    /**
     * @dataProvider validSourceProvider
     */
    public function testExtractReturnsNormalizedId(string $input, string $expectedId): void
    {
        $extractor = new YoutubeVideoIdExtractor();

        $this->assertSame($expectedId, $extractor->extract($input));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public function validSourceProvider(): iterable
    {
        yield 'direct id' => ['dQw4w9WgXcQ', 'dQw4w9WgXcQ'];
        yield 'direct id with spaces' => ['  dQw4w9WgXcQ  ', 'dQw4w9WgXcQ'];
        yield 'watch url' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'];
        yield 'watch url with extra params' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=42s', 'dQw4w9WgXcQ'];
        yield 'youtu.be url' => ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'];
        yield 'shorts url' => ['https://youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'];
        yield 'www shorts url' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'];
    }

    /**
     * @dataProvider invalidSourceProvider
     */
    public function testExtractReturnsNullForInvalidInput(string $input): void
    {
        $extractor = new YoutubeVideoIdExtractor();

        $this->assertNull($extractor->extract($input));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public function invalidSourceProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'spaces only' => ['   '];
        yield 'too short id' => ['abc123'];
        yield 'invalid url' => ['https://example.com/watch?v=dQw4w9WgXcQ'];
        yield 'watch without id' => ['https://www.youtube.com/watch?v='];
        yield 'random text' => ['not-a-youtube-reference'];
    }
}
