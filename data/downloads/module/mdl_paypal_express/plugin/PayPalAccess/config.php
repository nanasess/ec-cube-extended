<?php
// {{{ requires
require_once(realpath(dirname( __FILE__)) . "/define.php");
require_once(PLUGIN_UPLOAD_REALDIR . PAYPAL_ACCESS_PLUGIN_NAME . "/class/pages/LC_Page_PayPalAccess_Config.php");

// }}}
// {{{ generate page

$objPage = new LC_Page_PayPalAccess_Config();
$objPage->init();
$objPage->process();
