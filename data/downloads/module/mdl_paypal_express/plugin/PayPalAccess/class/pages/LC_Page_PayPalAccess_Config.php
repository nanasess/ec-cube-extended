<?php
require_once(CLASS_EX_REALDIR . 'page_extends/admin/LC_Page_Admin_Ex.php');
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/helper/SC_Helper_PayPalAccess.php';
/**
 * PayPalAccessのページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_PayPalAccess_Config extends LC_Page_Admin_Ex {

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();
        $this->tpl_mainpage =  PLUGIN_UPLOAD_REALDIR . PAYPAL_ACCESS_PLUGIN_NAME . "/templates/config.tpl";
        $this->tpl_subtitle = 'PayPal Access プラグイン';
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {
        parent::process();
        $this->action();
        $this->sendResponse();
    }

    function action() {
        $objFormParam = new SC_FormParam_Ex();

        $this->initParam($objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();
        switch ($_POST['mode']) {
        case 'edit':
            $this->arrErr = $objFormParam->checkError();
            if (SC_Utils_Ex::isBlank($this->arrErr)) {
                $this->tpl_onload .= 'alert("登録完了しました。\nデザイン管理画面より、PayPal Access ボタンのブロックを配置してください。"); window.close();';
                $requires_revoke = $objFormParam->getValue('requires_revoke');
                $this->setUpNotnull($requires_revoke);
                SC_Helper_PayPalAccess::setConfig($objFormParam->getHashArray());
            }
            break;
        default:
            // データのロード
            $arrConfig = SC_Helper_PayPalAccess::getConfig();
            $objFormParam->setParam($arrConfig);

            if (SC_Utils_Ex::isBlank($objFormParam->getValue('requires_revoke'))) {
                $objFormParam->setValue('requires_revoke', PayPalAccess::REQUIRES_REVOKE_ENABLED);
            }

            break;
        }

        $this->check_ssl = $this->checkSSL();
        $this->arrForm = $objFormParam->getFormParamList();
        $this->setTemplate($this->tpl_mainpage);
    }

    /**
     *  パラメータ情報の初期化
     */
    function initParam(&$objFormParam) {
        $objFormParam->addParam("App ID", "app_id", MTEXT_LEN, "a", array("MAX_LENGTH_CHECK", "EXIST_CHECK"));
        $objFormParam->addParam("App Secret", "app_secret", MTEXT_LEN, "a", array("MAX_LENGTH_CHECK", "EXIST_CHECK"));
        $objFormParam->addParam("「カナ(姓/名)・性別」の入力", "requires_revoke", 1, "n", array("MAX_LENGTH_CHECK", "NUM_CHECK"));
        $objFormParam->addParam("サンドボックスの使用", "use_sandbox", 1, "n", array("MAX_LENGTH_CHECK", "NUM_CHECK"));
    }

    /**
     * HTTPS_URL の設定をチェックします.
     */
    protected function checkSSL() {
        if (strpos(HTTPS_URL, 'https') !== false) {
            return true;
        } else {
            if (defined('PAYPAL_ACCESS_PLUGIN_ALLOW_NOTSSL')
                && PAYPAL_ACCESS_PLUGIN_ALLOW_NOTSSL) {
                return true;
            }
            return false;
        }
    }

    /**
     * NOT NULL の設定/解除をします
     */
    protected function setUpNotnull($requires_revoke) {
        $mode = ($requires_revoke == PayPalAccess::REQUIRES_REVOKE_ENABLED) ? 'drop' : 'set';

        $file = realpath(dirname( __FILE__)) . '/../../sql/alter_table_' . $mode . '_' .DB_TYPE . '.sql';
        SC_Helper_PayPalAccess::executeSQL($file);
    }
}