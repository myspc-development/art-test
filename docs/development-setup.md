# Development Setup

ArtPulse currently supports **PHP 8.2–8.4**. Ensure a compatible PHP version is installed before continuing.

Install Composer dependencies and WordPress PHPUnit tools:

```bash
composer install    # PHP libraries
```

Install Node packages used for bundling JavaScript and compiling SCSS, then build the bundled assets:

```bash
npm install
npm run build
```

The `scripts/` directory stores optional scaffolding and release helpers used during early development. These scripts are **not** required when setting up the plugin from source.

Before running the tests for the first time, execute the environment setup script to fetch WordPress and install dependencies:

```bash
bash scripts/setup-env.sh
```
The script requires the `svn` command. Install Subversion if it is not available. This script downloads WordPress and installs Composer dependencies. When the `CI` environment variable is present it runs `composer install` with non‑interactive options, making it suitable for automated pipelines.

Run the test suite and coding standards checks with:

```bash
vendor/bin/phpunit
composer sniff
```

The `package.json` file defines scripts for building production assets. To compile the SCSS, bundle the React sources and build blocks run:

```bash
npm run build
```
PostCSS reads `postcss.config.js` from the repository root, so no additional configuration is required under `wp-content/plugins`. This command compiles the SCSS, bundles blocks and packages the React-based admin scripts via Rollup. Run it whenever files such as `sidebar-taxonomies.jsx`, `advanced-taxonomy-filter-block.jsx`, `filtered-list-shortcode-block.jsx` or `ajax-filter-block.jsx` are modified so the corresponding `.js` files are regenerated.

To create a distributable archive execute the Composer `zip` script:

```bash
composer zip
```

The zip file is placed in the `release/` directory.

## WebSocket Server

The real-time message server uses a JWT for authentication. Set the `JWT_SECRET` environment variable to a long random string before running the server. You can start it with `node server/ws-server.js` or via the npm script `npm run ws`.

To configure the server create a `.env` file in the project root. Define the secret and optionally the port:

```bash
JWT_SECRET=change_me_to_a_long_random_string
# PORT=3001
```

`JWT_SECRET` must be at least ten characters long or the server will abort at startup. Tokens sent by clients **must** include an `exp` claim which controls when the token expires. If `PORT` is omitted the server defaults to `3001`.

For production deployments the server should run behind a TLS‑terminating proxy (such as Nginx) or be updated to serve over HTTPS with valid certificates. Define `JWT_SECRET` and optional `PORT` values in your `.env` file and configure the proxy to forward WebSocket requests to this port. See [`server/README.md`](../server/README.md) for a brief deployment overview.

### Troubleshooting

- **Cannot connect:** ensure the server is running and that clients are using the correct WebSocket URL including the port value.
- **Unauthorized errors:** verify the JWT used by the client was signed with the same `JWT_SECRET` set for the server.
- **Connection closed immediately:** some corporate proxies and firewalls block WebSocket traffic. Try another network or adjust proxy settings.
- **500 errors on admin pages:** the plugin's `vendor/` directory is missing. Run `composer install` in the plugin root before activating it as noted in the [README](../README.md).

## Test Environment Variables

Database credentials for the WordPress test suite must be supplied via environment variables. Define these before running `bash scripts/setup-env.sh` or `vendor/bin/phpunit`:

- `DB_NAME` – name of the test database
- `DB_USER` – database user
- `DB_PASSWORD` – user password
- `DB_HOST` – database host
- `DB_CHARSET` – optional character set
- `DB_COLLATE` – optional collation

You may also set `WORDPRESS_TARBALL` to the path of a local WordPress archive if you need an offline setup. If any variables are omitted the test bootstrap will fail to connect to the database.

## CI Workflow

The `plugin-release.yml` workflow in `.github/workflows/` installs Composer and npm dependencies, runs unit tests and builds production assets. Composer caches are stored under `~/.composer/cache` while npm caches use `~/.npm`. The cache keys include `composer.lock` and `package-lock.json` so dependencies are re-downloaded only when these files change.
