<?php

return [
    'pluginVersion' => 'v1.1.0',
    'pluginName' => 'Zen Cart Debug Bar',
    'pluginDescription' => 'A lightweight debug bar scaffold for admin/storefront diagnostics, gated for development and trusted admin visibility.',
    'pluginAuthor' => 'Ian Wilson(wilt)',
    'pluginId' => 2436,
    'zcVersions' => [],
    'changelog' => 'Hardened storefront access behind a development-only gate, added admin permission gating, and redacted literal SQL values in query output.',
    'github_repo' => 'https://github.com/zcwilt/zencart-debugbar',
    'pluginGroups' => ['developer-tools', 'debugging'],
];
