# Privatus Analytics for Neos

**Real visitor numbers for your Neos site. No cookies, no cookie pop-up, no bots.**

You want to know if people visit your Neos site. But Google Analytics uses
cookies, so in many countries you must show a cookie pop-up first (the box that
asks visitors to say yes to cookies). Many people click no, so they never show
up in your numbers. Bots add fake visits on top.

This package adds the Privatus Analytics script to the head of your Neos pages.
The script sets no cookies and keeps no IP addresses. Bots are filtered out
before they are counted.

So you see how many real people came, which pages they looked at and where they
came from, on one simple dashboard. A report comes to your inbox each week, and
you can set alerts for when traffic jumps or drops. And you don't need a cookie
pop-up for your analytics.

**Free plan, no card:** 10,000 events a month (an event is a page view or an action you track) on up to 3 sites. [Start free at privatusanalytics.com](https://privatusanalytics.com).

- **No cookies.** No cookies and no hidden IDs on your visitors' devices, and no IP
  addresses are kept. Laws differ by country, so check your own legal advice.
- **Real people, not bots.** Known bots, data centers and spam visits are left
  out on every plan.
- **Small and fast.** The script is about 3 KB when compressed.

## What this package does

- Adds the Privatus Analytics tracking script to the `<head>` of every page
  that uses `Neos.Neos:Page`.
- Prints nothing until you set a valid site ID.
- Prints nothing in the Neos backend, so your own editing is not counted.
- Only prints the browser script. It never sends events from your server and
  sets no cookies.

You need a Privatus Analytics account to see reports, and there is a free plan.
Sign up at https://privatusanalytics.com.

## Requirements

- Neos 9.0 or later.
- PHP 8.2 or later (Neos 9.2 needs PHP 8.4).
- A Privatus Analytics site ID. It starts with `pa_`.

## Installation

```bash
composer require privatus/neos-analytics
./flow flow:cache:flush
```

## Setup

Add your site ID to `Configuration/Settings.yaml`:

```yaml
Privatus:
  NeosAnalytics:
    siteId: 'pa_YOURSITEID'
    modules: [engage, auto, vitals, clicks, ask]
```

Then clear the cache with `./flow flow:cache:flush`. The script now shows on
every page:

```html
<script defer src="https://privatusstats.com/js/pa.js" data-site="pa_YOURSITEID" data-modules="engage,auto,vitals,clicks,ask"></script>
```

### Modules

Modules measure extra things. Turn on the ones you want.

| Module | What it measures |
| --- | --- |
| `engage` | Time on page and scroll depth |
| `auto` | Outbound links, downloads, forms and 404 pages |
| `vitals` | Web Vitals (how fast pages load and react) |
| `clicks` | Click maps |
| `ask` | On-site surveys |

### All settings

| Setting | What it does |
| --- | --- |
| `siteId` | Your site ID. Required. It must look like `pa_AbC123`. |
| `enabled` | `false` prints no script. Handy in `Configuration/Development/Settings.yaml`. |
| `modules` | The modules above. Defaults to `[engage]`. An empty list turns them all off. |
| `excludedPaths` | Paths that are never counted, for example `['/private/*']`. |
| `domains` | Only count visits on these host names, for example `[example.com]`. |
| `spa` | How page changes without a reload are found: `auto`, `history`, `hash` or `off`. |
| `manual` | `true` turns off the automatic page view. You then call `privatus.pageview()` yourself. |
| `scriptHost` | The address that serves `/js/pa.js`. Keep the default unless you host your own copy. |

See the [tracker attributes](https://privatusanalytics.com/docs/tracker/attributes)
for more on each one.

### Set the site ID in the inspector (optional)

Editors can set the site ID and modules on the site's home page, with no code.
Add the mixin to your site node type, for example in your site package's
`NodeTypes` folder:

```yaml
'Vendor.Site:Document.HomePage':
  superTypes:
    'Privatus.NeosAnalytics:Mixin.SiteSettings': true
```

Open the home page in the Neos backend. A **Privatus Analytics** group now
shows in the inspector, on the Meta tab. A site ID set there wins over
`Settings.yaml`. Leave a field empty to use `Settings.yaml`. Each site in a
multi-site setup can have its own site ID this way.

### Caching

The tag is cached with the site node, so a change in the inspector shows up on
every page right away. After you change `Settings.yaml`, run
`./flow flow:cache:flush`.

### Use the tag in your own Fusion

The package adds the tag to `Neos.Neos:Page` for you. If your pages use
something else, add the prototype yourself:

```
head.privatusAnalytics = Privatus.NeosAnalytics:TrackingTag
```

## External service

After you set a site ID, every page loads `https://privatusstats.com/js/pa.js`
in the visitor's browser. The script sends the page address (query parameters
are removed except campaign parameters and any you allow), the referrer, the
browser language, the screen width, the time zone name and event names to
`https://privatusstats.com/api/event`. The visitor's IP address is read in
memory to find a rough country and to count unique visits, and it is not
stored.

- Terms of service: https://privatusanalytics.com/legal/terms
- Privacy policy: https://privatusanalytics.com/legal/privacy

## Tests

Unit tests live in `Tests/Unit`. Run them from a Neos 9 project:

```bash
bin/phpunit -c Build/BuildEssentials/PhpUnit/UnitTests.xml Packages/Application/Privatus.NeosAnalytics/Tests/Unit
```

CI also builds a Neos 9 site with the demo content and checks the tag is in
the page head.

## License

MIT. See `LICENSE`.
