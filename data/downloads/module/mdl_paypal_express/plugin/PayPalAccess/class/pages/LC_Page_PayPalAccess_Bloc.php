<?php
require_once CLASS_EX_REALDIR . 'page_extends/frontparts/bloc/LC_Page_FrontParts_Bloc_Ex.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/openid_connect/include.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/PayPalAccessClaims.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/PayPalAccessClient.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/helper/SC_Helper_PayPalAccess.php';

/**
 * PayPalAccessのブロック
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_PayPalAccess_Bloc extends LC_Page_FrontParts_Bloc_Ex {

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        $this->skip_load_page_layout = true;
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {
        $this->action();
        $this->sendResponse();
    }

    function action() {
        $arrConfig = SC_Helper_PayPalAccess::getConfig();
        $objClient = PayPalAccessClient::getInstance($arrConfig['app_id'], $arrConfig['app_secret'],
                                                     SC_Helper_PayPalAccess::useSandbox());
        $objClient->setScopesArray(array(PayPalAccessScope::OPENID,
                                         PayPalAccessScope::PROFILE,
                                         PayPalAccessScope::ADDRESS,
                                         PayPalAccessScope::EMAIL,
                                         PayPalAccessScope::PHONE,
                                         PayPalAccessScope::EXTENDED_SCOPE));
        $this->appid = $arrConfig['app_id'];
        $this->use_sandbox = SC_Helper_PayPalAccess::useSandbox();
        $this->returnurl = HTTPS_URL . 'plugin/' . PAYPAL_ACCESS_PLUGIN_NAME;
        $this->scopes = $objClient->getScopes();
    }
}