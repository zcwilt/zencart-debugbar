<?php

require 'includes/application_top.php';
require DIR_WS_INCLUDES . 'html_header.php';
?>
<!doctype html>
<html <?= HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<div class="container-fluid">
    <h1><?= defined('HEADING_TITLE') ? HEADING_TITLE : 'Debug Bar'; ?></h1>
    <div class="alert alert-info">
        <?= defined('TEXT_DEBUG_BAR_INTRO') ? TEXT_DEBUG_BAR_INTRO : 'This is the starter admin page for the Zen Cart Debug Bar plugin.'; ?>
    </div>
    <p><?= defined('TEXT_DEBUG_BAR_CONFIG_HINT') ? TEXT_DEBUG_BAR_CONFIG_HINT : 'Use the Configuration menu entry to manage plugin settings.'; ?></p>
</div>
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
