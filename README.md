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
- message-stack summary and grouped message details
- template/language PHP include order for the current request
- copy-to-clipboard buttons for expandable diagnostic sections
- quick hide/show toggle remembered in the browser

## Configuration

The plugin creates a `Debug Bar` configuration group in admin.

Current defaults are:

- `DEBUG_BAR_ENABLED = true`
- `DEBUG_BAR_ADMINS_ONLY = false`
- `DEBUG_BAR_SHOW_IN_ADMIN = true`
- diagnostic sections enabled by default

Available settings let you toggle individual sections on or off:

- timer
- memory
- render in admin
- session
- request
- server
- notifier trace
- database query summary
- message stack
- template/language file load order

## Notes

- This plugin is intended for development and debugging use.
- The hide/show toggle is stored in browser `localStorage`.
- If new configuration keys are added in code, the plugin install or upgrade logic needs to run before those keys appear in admin configuration.
- `DEBUG_BAR_SHOW_FILE_LOAD_ORDER` defaults to visible when the constant is missing, so existing local installs can use the panel before rerunning install/upgrade.

## Future ideas

- cart and checkout context
- selected configuration/environment flags
- filtered cookie visibility
- richer SQL diagnostics
