<?php
$realpath = dirname(__FILE__);
$scheme = "http";
if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") {
    $scheme = "https";
}
if ($_SERVER["SERVER_PORT"] == 80 || $_SERVER["SERVER_PORT"] == 443) {
    $location = "://" . $_SERVER["SERVER_NAME"] . "/";
    $http_location = $scheme . "://" . $_SERVER["SERVER_NAME"] . "/";
    /*
    if (file_get_contents('https' . $location) !== false) {
        $https_location = 'https' . $location;
    } else {
        $https_location = $http_location;
    }
    */
    $https_location = $http_location;
} else {
    $http_location = $scheme . "://" . $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . "/";
    $https_location = $http_location;
}

$define_php = $realpath . './define.php';
$webmatrix_php = $realpath . './webmatrix.php';
if (file_exists($define_php)) {
    require_once($define_php);

    if (file_exists($webmatrix_php)) {
        $subject = file_get_contents($webmatrix_php);
        preg_match("|/\\*\\s*mysql://([^:]*):([^@]*)@([^/]*)/([^;]*);\\*/|", $subject, $matches);
        list($all, $db_user, $db_password, $db_server, $db_name) = $matches;

        define('ECCUBE_INSTALL', 'ON');
        define('ROOT_URLPATH', '/');
        define('HTTP_URL', $http_location);
        define('HTTPS_URL', $https_location);
        define('DOMAIN_NAME', '');
        define('DB_TYPE', 'mysql');
        define('DB_USER', $db_user);
        define('DB_PASSWORD', $db_password);
        define('DB_SERVER', $db_server);
        define('DB_NAME', $db_name);
        define('DB_PORT', '');
    }
}
