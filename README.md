# Zen Cart Debug Bar

Zen Cart Debug Bar adds a lightweight debug panel for local development and troubleshooting. It renders at the bottom of storefront and admin pages and surfaces common request diagnostics without needing to dig through templates or dump variables manually.

## Current features

- request timer
- PHP memory usage
- session summary and expandable `$_SESSION` dump
- request summary with expandable `$_GET` and `$_POST` dumps
- server summary with expandable `$_SERVER` dump
- route/page context including `page_base`, `main_page`, template, language, currency, and login state
- admin page context including current PHP page, action, cmd, and admin session details
- notifier/event trace for the current request
- SQL query count and total SQL time
- SQL Query Summary grouped by normalized query pattern, including counts and timing totals
- Top 20 Slowest Query Executions for the current request
- message-stack summary and grouped message details
- template/language PHP include order for the current request
- copy-to-clipboard buttons for expandable diagnostic sections
- quick hide/show toggle remembered in the browser

## Configuration

The plugin creates a `Debug Bar` configuration group in admin.

Important storefront behavior change:

- The storefront debug bar no longer appears just because the plugin is installed or `DEBUG_BAR_ENABLED` is set to `true`.
- The storefront debug bar now stays hidden unless `DEBUG_BAR_ENVIRONMENT` is defined as `development` in a local `includes/extra_configures/` file.
- This is intentional and fail-closed. On a fresh install or upgrade, the storefront bar will disappear until that local environment constant is added.

Example local gate:

```php
<?php
define('DEBUG_BAR_ENVIRONMENT', 'development');
```

Place that in a local file such as `includes/extra_configures/debug_bar_environment.php` on development systems only.

Current defaults are:

- `DEBUG_BAR_ENABLED = false`
- `DEBUG_BAR_ADMINS_ONLY = true`
- `DEBUG_BAR_SHOW_IN_ADMIN = true`
- diagnostic sections enabled by default
- `DEBUG_BAR_SHOW_SQL_QUERIES = true` on fresh installs, but legacy upgrades from versions earlier than `1.0.6` add the key with `false` so existing sites do not gain the panel automatically

The storefront observer now fails closed unless all of the following are true:

- the plugin is explicitly enabled
- it is running in catalog context
- `DEBUG_BAR_ADMINS_ONLY` allows the current request
- `DEBUG_BAR_ENVIRONMENT` is defined as `development` in a local `includes/extra_configures/` file

The `DEBUG_BAR_ENVIRONMENT` gate is intentionally outside the database and outside Plugin Manager configuration. If that constant is missing or set to anything other than `development`, the storefront bar does not render, even when the plugin is enabled in admin.

`DEBUG_BAR_ADMINS_ONLY` still matters after the environment gate passes:

- if `DEBUG_BAR_ADMINS_ONLY = true`, the storefront bar appears only for storefront requests that also carry an admin session
- if `DEBUG_BAR_ADMINS_ONLY = false`, the storefront bar can appear for a normal storefront visitor session

The admin observer renders only on authenticated admin requests that also have the `configDebugBarView` permission. That permission key is registered by the installer, but it is permission-only and does not appear as a menu item; grant it through Admin Profiles to trusted admin users. Superusers pass this gate automatically, while non-superusers need the explicit profile permission.

Available settings let you toggle individual sections on or off:

- timer
- memory
- render in admin
- session
- request
- server
- notifier trace
- database query summary
- detailed SQL query diagnostics
- message stack
- template/language file load order

`database query summary` and `detailed SQL query diagnostics` can be enabled independently.

## Notes

- This plugin is intended for development and debugging use.
- Do not enable it on a public storefront unless you intentionally want authenticated admin sessions to see request, session, SQL, and file-load diagnostics.
- If the storefront bar disappears after install or upgrade, check for the local `DEBUG_BAR_ENVIRONMENT` constant first. Missing that constant is now the expected reason the storefront bar stays hidden.
- The hide/show toggle is stored in browser `localStorage`.
- If new configuration keys are added in code, the plugin install or upgrade logic needs to run before those keys appear in admin configuration.
- `DEBUG_BAR_SHOW_FILE_LOAD_ORDER` defaults to visible when the constant is missing, so existing local installs can use the panel before rerunning install/upgrade.
- Detailed SQL query diagnostics depend on the `NOTIFY_QUERY_FACTORY_EXECUTE_END` core notifier. On older Zen Cart versions, the debug bar falls back to query count and total SQL time only.
- The SQL Query Summary groups normalized query patterns and shows redacted sample queries for each pattern.
- The slow-query panel shows the top 20 individual query executions by elapsed time, with literal values redacted.
- The `configDebugBarView` admin permission is registered by the installer and should be granted only to trusted profiles that need to see the debug bar output. Superusers already have access automatically.
