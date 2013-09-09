<?php
require_once '../../require.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/pages/LC_Page_PayPalAccess_Authorization.php';

$objPage = new LC_Page_PayPalAccess_Authorization();
$objPage->init();
$objPage->process();
