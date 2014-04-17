<?php

/*
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("HTTP/1.1 400 Bad Request");
    exit;
}
*/
require_once(realpath(dirname( __FILE__)) . '/../../require.php');
require_once(PLUGIN_UPLOAD_REALDIR . 'WindowsAzureBlob/define.php');

$file_path = '';
if (isset($_REQUEST['file_path'])) {
    $file_path = $_REQUEST['file_path'];
} else {
    sendNotfound();
    exit(1);
}

if (strpos($file_path , IMAGE_SAVE_URLPATH) === false) {
    sendNotfound();
    exit(1);
}

require_once(PLUGIN_UPLOAD_REALDIR . AZURE_BLOB_PLUGIN_NAME . '/class/helper/SC_Helper_AzureBlob.php');
$objBlob = SC_Helper_AzureBlob::getInstance();
$objFile = new BlobFile();
$objFile->file_name = str_replace(IMAGE_SAVE_URLPATH, '', $file_path);
$objFile->real_filepath = IMAGE_SAVE_REALDIR . $objFile->file_name;
$objBlob->copyToBlob($objFile);

echo $file_path;

function sendNotfound() {
    header("HTTP/1.1 404 File not found");
    exit(1);
}
