# Copilot / AI contributor guidance for Cloudflare extension

Purpose: short, actionable guidance to help AI agents be productive in this codebase.

Big picture
- This is a MediaWiki extension that purges Cloudflare cache on page/file events.
- Event sources: hooks in `extension.json` map events to `HookHandler` (includes/HookHandler.php).
- Purge implementation: `CloudflareAPIRequester` (includes/CloudflareAPIRequester.php) calls Cloudflare API.
- Service wiring: `includes/ServiceWiring.php` registers `CloudflareAPIRequester` as a service used by hooks.
- Event relaying: `EventRelayer` subclass (includes/EventRelayer.php) integrates with the EventRelayer system for async purges.

Key files to inspect
- `extension.json` — declares hooks, services, config keys and `ServiceWiringFiles`.
- `composer.json` — composer scripts: `composer test`, `composer run phan`, `composer run phpcs`, `composer run fix`.
- `includes/HookHandler.php` — primary hook implementations for PageSave/PageDelete/PageMove and file purges.
- `includes/CloudflareAPIRequester.php` — HTTP interaction with Cloudflare; uses `HttpRequestFactory` + `Logger`.
- `includes/EventRelayer.php` — groups events and delegates to the requester using main config flags.
- `includes/ServiceWiring.php` — registers the service; follow this pattern for other services.

Project-specific conventions and notes
- Namespace: `MediaWiki\\Extension\\Cloudflare\\` mapped to `includes/` (PSR-4). Keep this mapping intact.
- Prefer wiring shared dependencies via `ServiceWiring.php` (container factory) rather than global singletons.
- Config keys: `CloudflareEmail`, `CloudflareAPIKey`, `CloudflareZoneID`, `CloudflarePurgePage`, `CloudflarePurgeFile` (set in LocalSettings.php).
- Logging: use MediaWiki `LoggerFactory` (see `ServiceWiring.php`) and avoid throwing raw exceptions in hooks — prefer logging.

Developer workflows (discoverable)
- Install deps: `composer install` (this is a MediaWiki extension; tests run in a MediaWiki test harness if added).
- Linters & static checks: `composer test` runs `parallel-lint`, `phpcs`, and `minus-x`.
- Fixers: `composer run fix` runs `minus-x fix .` then `phpcbf`.
- Phan: `composer run phan`.
- Unit tests: `phpunit` is configured in `phpunit.xml.dist`, but the file points to `tests/phpunit/unit` and `src` — repo currently uses `includes/`; update phpunit config before adding tests.

Patterns for changes
- To add a new service: add a factory to `includes/ServiceWiring.php` and list it in `extension.json` ServiceWiringFiles if needed.
- To react to a new hook: add the hook name in `extension.json` -> `Hooks` and implement the handler method in `includes/HookHandler.php` (or add a new handler class wired via services).
- To call Cloudflare API: reuse `CloudflareAPIRequester` via DI (service name `CloudflareAPIRequester`). Do not instantiate with `new` except in `ServiceWiring.php` factories.

Debugging tips
- Missing config: `CloudflareAPIRequester::cachePurge` throws `MWException` when config keys are empty — ensure LocalSettings.php has values.
- Inspect logs: logger channel is `cloudflare` (see `ServiceWiring.php`) — check MediaWiki logs for errors from Cloudflare calls.
- Event flow: Hooks -> `HookHandler` -> (direct call) or `EventRelayer` -> `CloudflareAPIRequester`.

What to update here
- If you add new services, hooks, or change config keys, update this file to reflect the new integration points.
- If phpunit layout changes (move to `src/` or `includes/`), update `phpunit.xml.dist`.

If anything is unclear or you'd like a different focus (tests, CI, or adding PHPStan/phan config), tell me which area to expand.
