<?php

declare(strict_types=1);

namespace Privatus\NeosAnalytics\Eel;

use Neos\Eel\ProtectedContextAwareInterface;
use Privatus\NeosAnalytics\TagBuilder;

/**
 * Eel helper, available in Fusion as Privatus.Analytics.
 */
class PrivatusAnalyticsHelper implements ProtectedContextAwareInterface
{
    /**
     * The tracking script tag, or an empty string when there is no valid site ID.
     *
     * @param array<string, mixed>|null $settings The Privatus.NeosAnalytics settings.
     */
    public function tag(?array $settings, mixed $siteId = null, mixed $modules = null): string
    {
        return TagBuilder::build($settings ?? [], $siteId, $modules);
    }

    /**
     * @param string $methodName
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return $methodName === 'tag';
    }
}
