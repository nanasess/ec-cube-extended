<?php
require_once '../../require.php';
require_once PLUGIN_UPLOAD_REALDIR . 'DroppedItemsNoticer/class/pages/LC_Page_Admin_Mail_DroppedItemsNoticer.php';

$objPage = new LC_Page_Admin_Mail_DroppedItemsNoticer();
$objPage->init();
register_shutdown_function(array($objPage, 'destroy'));
$objPage->process();
