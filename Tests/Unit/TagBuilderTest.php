<?php

declare(strict_types=1);

namespace Privatus\NeosAnalytics\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Privatus\NeosAnalytics\Eel\PrivatusAnalyticsHelper;
use Privatus\NeosAnalytics\TagBuilder;

final class TagBuilderTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function settings(array $overrides = []): array
    {
        return $overrides + [
            'siteId' => 'pa_TEST123',
            'enabled' => true,
            'modules' => ['engage'],
            'excludedPaths' => [],
            'domains' => [],
            'spa' => 'auto',
            'manual' => false,
            'scriptHost' => 'https://privatusstats.com',
        ];
    }

    public function testDefaultTag(): void
    {
        self::assertSame(
            '<script defer src="https://privatusstats.com/js/pa.js" data-site="pa_TEST123" data-modules="engage"></script>',
            TagBuilder::build($this->settings()),
        );
    }

    public function testAllModulesAndOptions(): void
    {
        $html = TagBuilder::build($this->settings([
            'modules' => ['engage', 'auto', 'vitals', 'clicks', 'ask', 'bogus'],
            'excludedPaths' => ['/private/*', ' '],
            'domains' => 'example.com, www.example.com',
            'spa' => 'history',
            'manual' => true,
            'scriptHost' => 'https://stats.example.com/',
        ]));
        self::assertStringContainsString('src="https://stats.example.com/js/pa.js"', $html);
        self::assertStringContainsString('data-modules="engage,auto,vitals,clicks,ask"', $html);
        self::assertStringContainsString('data-exclude="/private/*"', $html);
        self::assertStringContainsString('data-domains="example.com,www.example.com"', $html);
        self::assertStringContainsString('data-spa="history"', $html);
        self::assertStringContainsString('data-manual="true"', $html);
    }

    public function testEmptyModuleListTurnsModulesOff(): void
    {
        self::assertStringContainsString('data-modules=""', TagBuilder::build($this->settings(['modules' => []])));
    }

    public function testNoTagWithoutValidSiteId(): void
    {
        self::assertSame('', TagBuilder::build($this->settings(['siteId' => ''])));
        self::assertSame('', TagBuilder::build($this->settings(['siteId' => 'abc'])));
        self::assertSame('', TagBuilder::build($this->settings(['siteId' => 'pa_x"><script>alert(1)</script>'])));
        self::assertSame('', TagBuilder::build($this->settings(['siteId' => 'pa_ab-c'])));
    }

    public function testDisabled(): void
    {
        self::assertSame('', TagBuilder::build($this->settings(['enabled' => false])));
    }

    public function testSiteNodeValuesWin(): void
    {
        $html = TagBuilder::build($this->settings(), 'pa_NODE1', ['vitals', 'clicks']);
        self::assertStringContainsString('data-site="pa_NODE1"', $html);
        self::assertStringContainsString('data-modules="vitals,clicks"', $html);
    }

    public function testEmptyOrBadSiteNodeValuesFallBack(): void
    {
        $html = TagBuilder::build($this->settings(), '', []);
        self::assertStringContainsString('data-site="pa_TEST123"', $html);
        self::assertStringContainsString('data-modules="engage"', $html);

        $html = TagBuilder::build($this->settings(), 'nope', null);
        self::assertStringContainsString('data-site="pa_TEST123"', $html);
    }

    public function testBadHostAndSpaFallBack(): void
    {
        $html = TagBuilder::build($this->settings([
            'scriptHost' => 'javascript:alert(1)',
            'spa' => 'weird',
        ]));
        self::assertStringContainsString('src="https://privatusstats.com/js/pa.js"', $html);
        self::assertStringNotContainsString('data-spa', $html);

        $html = TagBuilder::build($this->settings(['scriptHost' => 'https://example.com/path']));
        self::assertStringContainsString('src="https://privatusstats.com/js/pa.js"', $html);
    }

    public function testListValuesAreEscaped(): void
    {
        $html = TagBuilder::build($this->settings(['excludedPaths' => ['/a"><b>']]));
        self::assertStringContainsString('data-exclude="/a&quot;&gt;&lt;b&gt;"', $html);
    }

    public function testEelHelper(): void
    {
        $helper = new PrivatusAnalyticsHelper();
        self::assertTrue($helper->allowsCallOfMethod('tag'));
        self::assertFalse($helper->allowsCallOfMethod('other'));
        self::assertSame('', $helper->tag(null));
        self::assertStringContainsString('pa_TEST123', $helper->tag($this->settings()));
    }
}
