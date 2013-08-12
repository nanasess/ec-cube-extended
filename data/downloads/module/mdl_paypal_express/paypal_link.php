<?php
/*
 * Copyright(c) 2000-2011 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 */
require_once(realpath(dirname( __FILE__)) . "/LC_Page_Mdl_PaypalExpress_Helper_Link.php");

$objPage = new LC_Page_Mdl_PaypalExpress_Helper_Link();
$objPage->init();
$objPage->process();
?>