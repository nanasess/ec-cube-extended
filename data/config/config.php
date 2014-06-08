<?php
$realpath = dirname(__FILE__);
define('ROOT_URLPATH', '/');

// for Azure
if (strpos($_SERVER['SERVER_NAME'], 'localhost') === false) {
    $location = "//" . $_SERVER["SERVER_NAME"] . ROOT_URLPATH;
    $http_location = 'http:' . $location;
    $https_location = 'https:' . $location;
}
// for WebMatrix
else {
    $http_location = "http://" . $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . ROOT_URLPATH;
    $https_location = $http_location;
}

define('HTTP_URL', $http_location);
define('HTTPS_URL', $https_location);
define('DOMAIN_NAME', '');

$define_php = $realpath . './define.php';
$webmatrix_php = $realpath . './webmatrix.php';
if (file_exists($define_php)) {
    require_once($define_php);

    if (file_exists($webmatrix_php)) {
        $subject = file_get_contents($webmatrix_php);
        preg_match("|/\\*\\s*mysql://([^:]*):([^@]*)@([^/]*)/([^;]*);\\*/|", $subject, $matches);
        list($all, $db_user, $db_password, $db_server, $db_name) = $matches;

        define('DB_TYPE', 'mysql');
        define('DB_USER', $db_user);
        define('DB_PASSWORD', $db_password);
        define('DB_SERVER', $db_server);
        define('DB_NAME', $db_name);
        define('DB_PORT', '');
    }
}
