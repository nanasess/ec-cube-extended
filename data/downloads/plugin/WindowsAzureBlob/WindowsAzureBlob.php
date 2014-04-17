<?php
require_once(realpath(dirname( __FILE__)) . '/define.php');
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname( __FILE__)) . '/pear');
/**
 * Windows Azure Blob 対応プラグイン
 *
 * @author Kentaro Ohkouchi
 */
class WindowsAzureBlob extends SC_Plugin_Base {

    /**
     * コンストラクタ.
     */
    public function __construct(array $arrSelfInfo) {
        parent::__construct($arrSelfInfo);
    }

    function install($arrPlugin) {
        if(copy(PLUGIN_UPLOAD_REALDIR . AZURE_BLOB_PLUGIN_NAME . '/logo.png', PLUGIN_HTML_REALDIR . AZURE_BLOB_PLUGIN_NAME . '/logo.png') === false);
    }

    function uninstall($arrPlugin) {
        // unsupported.
    }


    function enable($arrPlugin) {
    }

    function disable($arrPlugin) {
        // nop
    }

    function loadClassFileChange(&$classname, &$classpath) {
    }
}
