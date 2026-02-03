This devcontainer provides a reproducible environment for developing the Cloudflare MediaWiki extension.

What it supplies
- PHP 7.4 (from the official devcontainer PHP image)
- Composer (preinstalled in the base image)
- Common utilities: `git`, `unzip`, `ca-certificates`

On first open the container runs:

```
composer install --no-interaction
```

Common commands

Run tests:
```
vendor/bin/phpunit -c phpunit.xml.dist
```

Run linters/formatters:
```
composer test
composer run fix
```
