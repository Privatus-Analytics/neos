<?php

declare(strict_types=1);

namespace Privatus\NeosAnalytics;

use Privatus\Analytics\Config;
use Privatus\Analytics\Tag;

/**
 * Turns the package settings, and the optional values from the site node,
 * into the tracking script tag.
 *
 * It only prints the browser script. It never sends events from the server.
 */
final class TagBuilder
{
    public const SITE_ID_PATTERN = '/^pa_[A-Za-z0-9]+$/';

    public const HOST_PATTERN = '#^https?://[A-Za-z0-9.-]+(:[0-9]{1,5})?/?$#';

    /**
     * @param array<string, mixed> $settings  The Privatus.NeosAnalytics settings.
     * @param mixed                $siteId    The site ID from the site node. Empty means use the settings.
     * @param mixed                $modules   The modules from the site node. Empty means use the settings.
     */
    public static function build(array $settings, mixed $siteId = null, mixed $modules = null): string
    {
        if (array_key_exists('enabled', $settings) && !$settings['enabled']) {
            return '';
        }

        $id = self::siteId($siteId);
        if ($id === '') {
            $id = self::siteId($settings['siteId'] ?? null);
        }
        if ($id === '') {
            return '';
        }

        $chosen = self::modules($modules);
        if ($chosen === null) {
            $chosen = self::modules($settings['modules'] ?? null) ?? [];
        }

        $host = trim(is_string($settings['scriptHost'] ?? null) ? $settings['scriptHost'] : '');
        if ($host === '' || preg_match(self::HOST_PATTERN, $host) !== 1) {
            $host = Config::DEFAULT_HOST;
        }

        $spa = is_string($settings['spa'] ?? null) ? $settings['spa'] : 'auto';

        $config = new Config(
            siteId: $id,
            scriptHost: $host,
            modules: $chosen,
            excludedPaths: self::strings($settings['excludedPaths'] ?? []),
            domains: self::strings($settings['domains'] ?? []),
            spa: in_array($spa, Config::SPA_MODES, true) ? $spa : null,
            manual: (bool) ($settings['manual'] ?? false),
            enabled: true,
        );

        return (new Tag($config))->render();
    }

    /** A valid site ID, or '' when the value is empty or wrong. */
    public static function siteId(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }
        $value = trim($value);

        return preg_match(self::SITE_ID_PATTERN, $value) === 1 ? $value : '';
    }

    /**
     * The known modules in the given list, or null when the value is empty or not a list.
     *
     * @return list<string>|null
     */
    public static function modules(mixed $value): ?array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        if (!is_array($value) || $value === []) {
            return null;
        }
        $wanted = array_map(static fn (mixed $item): string => is_scalar($item) ? trim((string) $item) : '', $value);

        return array_values(array_intersect(Config::MODULES, $wanted));
    }

    /**
     * @return list<string>
     */
    private static function strings(mixed $value): array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        $items = [];
        foreach ((array) $value as $item) {
            $item = is_scalar($item) ? trim((string) $item) : '';
            if ($item !== '') {
                $items[] = $item;
            }
        }

        return $items;
    }
}
