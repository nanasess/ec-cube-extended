<?php

require_once '../data/config/config.php';
if (defined('ECCUBE_INSTALL')) {
    $installerFile = './install/index.php';
    if (file_exists($installerFile)) {
        unlink($installerFile);
    }
}
$url = 'http://' . urlencode($_SERVER['SERVER_NAME']) . '/';
if (isset($_SERVER['HTTP_REFERER'])
    && preg_match('!^(https?://[^\?]+)!', $_SERVER['HTTP_REFERER'], $m)
) {
    $url = $m[1];
}
header("Location: {$url}");