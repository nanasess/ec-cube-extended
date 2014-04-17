<?php
define('ECCUBE_INSTALL', 'ON');
define('ROOT_URLPATH', $_SERVER['ROOT_URLPATH']);
// if ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) {
//     $location = '//' . $_SERVER['SERVER_NAME'] . ROOT_URLPATH;
// } else {
//     $location = '//' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . ROOT_URLPATH;
// }
$location = '//' . $_SERVER['SERVER_NAME'] . ROOT_URLPATH;
define('HTTP_URL', 'http:' . $location);
define('HTTPS_URL', 'https:' . $location); // TODO インストーラで設定する
define('DOMAIN_NAME', $_SERVER['DOMAIN_NAME']);
define('DB_TYPE', $_SERVER['DB_TYPE']);
define('DB_USER', $_SERVER['DB_USER']);
define('DB_PASSWORD', $_SERVER['DB_PASSWORD']);
define('DB_SERVER', $_SERVER['DB_SERVER']);
define('DB_NAME', $_SERVER['DB_NAME']);
define('DB_PORT', $_SERVER['DB_PORT']);
define('ADMIN_DIR', $_SERVER['ADMIN_DIR']);
define('ADMIN_FORCE_SSL', FALSE);
define('ADMIN_ALLOW_HOSTS', 'a:0:{}');
define('AUTH_MAGIC', $_SERVER['AUTH_MAGIC']);
define('PASSWORD_HASH_ALGOS', 'sha256');
define('MAIL_BACKEND', $_SERVER['MAIL_BACKEND']);
define('SMTP_HOST', $_SERVER['SMTP_HOST']);
define('SMTP_PORT', $_SERVER['SMTP_PORT']);
define('SMTP_USER', $_SERVER['SMTP_USER']);
define('SMTP_PASSWORD', $_SERVER['SMTP_PASSWORD']);
