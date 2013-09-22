<?php
require_once realpath(dirname(__FILE__)) . '/../../require.php';
require_once CLASS_EX_REALDIR . 'page_extends/frontparts/bloc/LC_Page_FrontParts_Bloc_Ex.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/define.php';
require_once(PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/helper/SC_Helper_PayPalAccess.php');

/**
 * Log In with PayPal のブロック
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_FrontParts_Bloc_PayPalAccess extends LC_Page_FrontParts_Bloc_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    public function action()
    {
        if (SC_Helper_PayPalAccess::loadConfig()) {
            $this->arrPayPalAccessConfig = SC_Helper_PayPalAccess::getConfig();
        }
    }
}

$objPage = new LC_Page_FrontParts_BLoc_PayPalAccess();
$objPage->blocItems = $params['items'];
$objPage->init();
$objPage->process();
