<?php
define('AZURE_BLOB_PLUGIN_NAME', 'WindowsAzureBlob');
define('ENDPOINT_PROTOCOL', 'https');
define('AZURE_BLOB_ACCOUNT_NAME', $_SERVER['AZURE_BLOB_ACCOUNT_NAME']);
define('AZURE_BLOB_ACCOUNT_KEY', $_SERVER['AZURE_BLOB_ACCOUNT_KEY']);
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname( __FILE__)) . '/pear');