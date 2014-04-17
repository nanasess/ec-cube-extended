<?php
$realpath = dirname(__FILE__);
$scheme = "http";
if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") {
    $scheme = "https";
}
$path = str_replace('index.php', '', $_SERVER["REQUEST_URI"]);
if ($_SERVER["SERVER_PORT"] == 80 || $_SERVER["SERVER_PORT"] == 443) {
    $location = $scheme . "://" . $_SERVER["SERVER_NAME"] . $path . "html/";
} else {
    $location = $scheme . "://" . $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $path . "html/";
}
$config_php = $realpath . '/data/config/config.php';
$webmatrix_php = $realpath . '/data/config/webmatrix.php';

if (file_exists($config_php)) {
    require_once($config_php);

    if (defined('ECCUBE_INSTALL') && ECCUBE_INSTALL == 'ON') {
        $subject = file_get_contents($webmatrix_php);
        preg_match("|/\*\s*mysql://([^:]*):([^@]*)@([^/]*)/([^;]*);\*/|", $subject, $matches);
        list($all, $db_user, $db_password, $db_server, $db_name) = $matches;
        $admin_force_ssl = ADMIN_FORCE_SSL ? 'TRUE' : 'FALSE';

        $config_data = "<?php\n"
            . "define ('ECCUBE_INSTALL', 'ON');\n"
            . "define ('HTTP_URL', '" . $location . "');\n"
            . "define ('HTTPS_URL', '" . $location . "');\n"
            . "define ('ROOT_URLPATH', '" . $path . "html/');\n"
            . "define ('DOMAIN_NAME', '');\n"
            . "define ('DB_TYPE', 'mysql');\n"
            . "define ('DB_USER', '" . $db_user . "');\n"
            . "define ('DB_PASSWORD', '" . $db_password . "');\n"
            . "define ('DB_SERVER', '" . $db_server . "');\n"
            . "define ('DB_NAME', '" . $db_name . "');\n"
            . "define ('DB_PORT', '');\n"
            . "define ('ADMIN_DIR', '" . ADMIN_DIR . "');\n"
            . 'define ("ADMIN_FORCE_SSL", ' . $admin_force_ssl . ');' . "\n"
            . "define ('ADMIN_ALLOW_HOSTS', '" . ADMIN_ALLOW_HOSTS . "');\n"
            . "define ('AUTH_MAGIC', '" . AUTH_MAGIC . "');\n"
            . "define ('PASSWORD_HASH_ALGOS', '" . PASSWORD_HASH_ALGOS . "');\n"
            . "define ('MAIL_BACKEND', '" . MAIL_BACKEND . "');\n"
            . "define ('SMTP_HOST', '" . SMTP_HOST . "');\n"
            . "define ('SMTP_PORT', '" . SMTP_PORT . "');\n"
            . "define ('SMTP_USER', '" . SMTP_USER . "');\n"
            . "define ('SMTP_PASSWORD', '" . SMTP_PASSWORD . "');\n"
            . "?>\n";
        if($fp = fopen($config_php,"w")) {
            fwrite($fp, $config_data);
            fclose($fp);
        }
    }
}

header("Location: " . $location . "index.php");
?>
