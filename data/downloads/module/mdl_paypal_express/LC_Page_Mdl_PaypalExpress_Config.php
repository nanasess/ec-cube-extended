<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2011 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


// {{{ requires
require_once(realpath(dirname( __FILE__)) . "/include.php");
require_once(realpath(dirname( __FILE__)) . "/SC_Helper_Paypal.php");
require_once(CLASS_EX_REALDIR . "page_extends/admin/LC_Page_Admin_Ex.php");

/**
 * PayPal決済モジュールのページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id: LC_Page_Mdl_PaypalExpress_Config.php 1383 2013-06-24 08:21:00Z nanasess $
 */
class LC_Page_Mdl_PaypalExpress_Config extends LC_Page_Admin_Ex {

    const PAYPAL_ACCESS_NAME = 'PayPalAccess';
    const DROPPED_ITEMS_NOTICER_NAME = 'DroppedItemsNoticer';

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();
        $this->tpl_mainpage = MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . "/config.tpl";
        $this->tpl_subtitle = 'PayPal Express Checkout 決済モジュール';
        if (version_compare(ECCUBE_VERSION, '2.12.0', '>=')) {
            $this->paypalaccess_flg = true;
            $this->check_ssl = $this->checkSSL();
            $this->droppeditemsnoticer_flg = true;
            if (!defined('PAYPAL_ACCESS_PLUGIN_NAME')) {
                require_once(realpath(dirname( __FILE__)) . "/plugin/" . self::PAYPAL_ACCESS_NAME . '/define.php');
            }
            if (!defined('DROPPED_ITEMS_NOTICER_PLUGIN_NAME')) {
                require_once(realpath(dirname( __FILE__)) . "/plugin/" . self::DROPPED_ITEMS_NOTICER_NAME . '/define.php');
            }
        }
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

    /**
     * Page のアクション.
     *
     * @return void
     */
    function action() {
        $objFormParam = new SC_FormParam_Ex();

        $this->initParam($objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();
        switch ($this->getMode()) {
        case 'edit':
            $this->arrErr = $objFormParam->checkError();
            if ($objFormParam->getValue('use_droppeditemsnoticer') == '1'
                && $objFormParam->getValue('use_express_btn') != '1') {
                $this->arrErr['use_express_btn'] = '※ カゴ落ち通知メルマガ配信機能を使用する場合は、「PayPalでチェックアウト」ボタンを使用する にチェックを入れてください。<br />';
            }
            if (SC_Utils_Ex::isBlank($this->arrErr)) {
                if ($this->registerPlugin()) {
                    SC_Helper_Paypal::registerPayments();
                    $arrConfig = SC_Helper_Paypal::getConfig();
                    $arrForm = $objFormParam->getHashArray();
                    if (isset($arrConfig['is_configured'])) {
                        $arrForm['is_configured'] = $arrConfig['is_configured'];
                    }
                    SC_Helper_Paypal::setConfig($arrForm);
                    if ($objFormParam->getValue('use_paypalaccess') == '1') {
                        $arrParams = array();
                        $arrParams['free_field1'] = $objFormParam->getValue('app_id');
                        $arrParams['free_field2'] = $objFormParam->getValue('app_secret');
                        $arrParams['free_field3'] = $objFormParam->getValue('requires_revoke');
                        if ($this->paypalaccess_flg == true && !$this->registerPayPalAccessPlugin($arrParams)) {
                            break;
                        }
                    } else {
                        $this->disablePayPalAccessPlugin();
                    }
                    if ($objFormParam->getValue('use_droppeditemsnoticer') == '1') {
                        $arrParams = array();
                        if ($this->droppeditemsnoticer_flg == true && !$this->registerDroppedItemsNoticerPlugin($arrParams)) {
                            break;
                        }
                    } else {
                        $this->disableDroppedItemsNoticerPlugin();
                    }
                    // 完了メール送信
                    // 初めて設定を行ったユーザのみ送信する.
                    $arrConfig = array();
                    $arrConfig = SC_Helper_Paypal::getConfig();
                    if (!isset($arrConfig['is_configured'])) {
                        $arrConfig['is_configured'] = 1;
                        SC_Helper_Paypal::setConfig($arrConfig);
                        $this->sendSettingsCompleteMail();
                    }
                    // 完了画面へリダイレクト
                    $arrQueryString = array();
                    $arrQueryString['mode'] = 'complete';
                    SC_Response_Ex::reload($arrQueryString);
                    SC_Response_Ex::actionExit();
                }
            }
            break;
        case 'complete':
            $this->tpl_mainpage = MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . "/config_complete.tpl";
            break;
        default:
            // データのロード
            $arrConfig = SC_Helper_Paypal::getConfig();
            if (!$arrConfig) {
                $arrConfig['use_express_btn'] = '1';
                if ($this->paypalaccess_flg == true) {
                    $arrConfig['use_paypalaccess'] = '1';
                }
                if ($this->droppeditemsnoticer_flg == true) {
                    $arrConfig['use_droppeditemsnoticer'] = '1';
                }
            }
            if (SC_Utils_Ex::isBlank($arrConfig['requires_revoke'])) {
                $arrConfig['requires_revoke'] = '1';
            }
            $objFormParam->setParam($arrConfig);
            break;
        }
        $this->arrForm = $objFormParam->getFormParamList();
        $this->setTemplate($this->tpl_mainpage);
    }

    function availablePluginInfo($path, $plugin_code) {
        $class_name = 'plugin_info_' . $plugin_code;
        if (!class_exists($class_name)) {
            $text = file_get_contents($path . 'plugin_info.php');
            $text = str_replace('plugin_info', 'plugin_info_' . $plugin_code, $text);
            $text = str_replace(array('<?php', '?>'), '', $text);
            eval($text);
        }
        return new ReflectionClass($class_name);
    }

    function installPlugin($plugin_code, $arrParams) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $arrExists = $objQuery->getRow('*', 'dtb_plugin', 'plugin_code = ?', array($plugin_code));
        $exists = is_array($arrExists) ? '1' : '0'; // 後方互換性のため
        $plugin_path = DATA_REALDIR . 'downloads/plugin/' . $plugin_code . '/';
        $plugin_html_path = HTML_REALDIR . 'plugin/' . $plugin_code . '/';
        foreach (array($plugin_path, $plugin_html_path) as $path) {
            if (!file_exists($path)) {
                if (!mkdir($path, 0777, true)) {
                    $this->arrErr['err'] = $path . ' の作成に失敗しました';
                    return false;
                }
            }
        }
        SC_Utils_Ex::copyDirectory(MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . '/plugin/' . $plugin_code . '/',
                                   $plugin_path);
        if (!file_exists($plugin_path . $plugin_code . '.php')) {
            $this->arrErr['err'] = $plugin_path . $plugin_code . '.php のコピーに失敗しました';
            return false;
        }

        $objReflection = $this->availablePluginInfo($plugin_path, $plugin_code);

        $arrParams['update_date'] = 'now()';
        if ($exists < 1) {
            $arrParams['plugin_id'] = $objQuery->nextVal('dtb_plugin_plugin_id');
            $arrParams['plugin_name'] = $objReflection->getStaticPropertyValue('PLUGIN_NAME');
            $arrParams['plugin_code'] = $objReflection->getStaticPropertyValue('PLUGIN_CODE');
            $arrParams['class_name'] = $objReflection->getStaticPropertyValue('CLASS_NAME');
            $arrParams['plugin_description'] = $objReflection->getStaticPropertyValue('DESCRIPTION');
            $arrParams['enable'] = PLUGIN_ENABLE_TRUE;
            $arrParams['create_date'] = 'now()';
            $objQuery->insert('dtb_plugin', $arrParams);

            $arrHookPoints = $objReflection->getStaticPropertyValue('HOOK_POINTS');
            $this->registerHookpoints($arrParams['plugin_id'], $arrHookPoints);

            require_once($plugin_path . $plugin_code . '.php');
            $objPlugin = new $plugin_code($arrParams);
            $objPlugin->install($arrParams);
            $objPlugin->enable($arrParams);
        } else {
            $arrParams['plugin_id'] = $arrExists['plugin_id'];
            require_once($plugin_path . $plugin_code . '.php');
            $objPlugin = new $plugin_code($arrParams);

            $arrHookPoints = $objReflection->getStaticPropertyValue('HOOK_POINTS');
            $objQuery->delete('dtb_plugin_hookpoint', 'plugin_id = ?', array($arrParams['plugin_id']));
            $this->registerHookpoints($arrParams['plugin_id'], $arrHookPoints);

            if ($arrExists['plugin_code'] == self::PAYPAL_ACCESS_NAME) {
                $arrExistsBloc = $objQuery->select('*', 'dtb_bloc', 'plugin_id = ?', array($arrExists['plugin_id']));
                if (SC_Utils_Ex::isBlank($arrExistsBloc)) {
                    $objPlugin->enable($arrParams);
                }
            } else {
                $objPlugin->enable($arrParams);
            }
            // free_field は初期化しない
            $arrParams['free_field1'] = $arrExists['free_field1'];
            $arrParams['free_field2'] = $arrExists['free_field2'];
            $arrParams['free_field3'] = $arrExists['free_field3'];
            $arrParams['free_field4'] = $arrExists['free_field4'];
            $arrParams['enable'] = PLUGIN_ENABLE_TRUE;
            $objQuery->update('dtb_plugin', $arrParams, 'plugin_code = ?', array($objReflection->getStaticPropertyValue('PLUGIN_CODE')));
        }
        SC_Utils_Ex::clearCompliedTemplate();
        return true;

    }

    function disablePlugin($plugin_code) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $arrExists = $objQuery->getRow('*', 'dtb_plugin', 'plugin_code = ?', array($plugin_code));

        if (!SC_Utils_Ex::isBlank($arrExists)) {
            $objQuery->update('dtb_plugin', array('enable' => PLUGIN_ENABLE_FALSE),
                              'plugin_code = ?', array($plugin_code));

            $plugin_path = DATA_REALDIR . 'downloads/plugin/' . $plugin_code . '/' . $plugin_code . '.php';
            if (file_exists($plugin_path)) {
                require_once $plugin_path;
                $objPlugin = new $plugin_code($arrExists);
                $objPlugin->disable($arrExists);
            }
        }
    }

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }

    /**
     *  パラメータ情報の初期化
     */
    function initParam(&$objFormParam) {
        $objFormParam->addParam("APIユーザー名", "api_user", MTEXT_LEN, "KVa", array("MAX_LENGTH_CHECK", "EXIST_CHECK"));
        $objFormParam->addParam("APIパスワード", "api_pass", MTEXT_LEN, "KVa", array("MAX_LENGTH_CHECK", "EXIST_CHECK"));
        $objFormParam->addParam("API署名", "api_signature", MTEXT_LEN, "KVa", array("MAX_LENGTH_CHECK", "EXIST_CHECK"));
        $objFormParam->addParam("Express Checkout ボタンの使用", "use_express_btn", 1, "n", array("MAX_LENGTH_CHECK", "NUM_CHECK"));
        $objFormParam->addParam("サンドボックスの使用", "use_sandbox", 1, "n", array("MAX_LENGTH_CHECK", "NUM_CHECK"));
        $objFormParam->addParam("PayPalAccess の使用", "use_paypalaccess", 1, "n", array("MAX_LENGTH_CHECK", "NUM_CHECK"));
        $objFormParam->addParam("カゴ落ち通知メルマガ配信機能 の使用", "use_droppeditemsnoticer", 1, "n", array("MAX_LENGTH_CHECK", "NUM_CHECK"));
        if ($_POST['use_paypalaccess'] == '1') {
            $objFormParam->addParam("App ID", "app_id", MTEXT_LEN, "a", array("MAX_LENGTH_CHECK", "EXIST_CHECK"));
            $objFormParam->addParam("App Secret", "app_secret", MTEXT_LEN, "a", array("MAX_LENGTH_CHECK", "EXIST_CHECK"));
            $objFormParam->addParam("「カナ(姓/名)・性別」の入力", "requires_revoke", 1, "n", array("MAX_LENGTH_CHECK", "NUM_CHECK"));
        } else {
            $objFormParam->addParam("App ID", "app_id", MTEXT_LEN, "a", array("MAX_LENGTH_CHECK"));
            $objFormParam->addParam("App Secret", "app_secret", MTEXT_LEN, "a", array("MAX_LENGTH_CHECK"));
            $objFormParam->addParam("「カナ(姓/名)・性別」の入力", "requires_revoke", 1, "n", array("MAX_LENGTH_CHECK", "NUM_CHECK"));
        }
    }

    /**
     * プラグインを登録する.
     *
     * @return boolean 成功した場合 true; 失敗した場合 false
     */
    function registerPlugin() {
        $plugin_dir = SC_Helper_Paypal::getPluginDir();
        if (!file_exists($plugin_dir)) {
            if (!SC_Utils_Ex::recursiveMkdir($plugin_dir)) {
                $this->arrErr['err'] = $plugin_dir . ' の作成に失敗しました';
                return false;
            }
        }
        $arrFiles = array('SC_Helper_Plugin_Paypal.php',
                          'cart.tpl',
                          'deliv_select.tpl',
                          'deliv_select_sphone.tpl',
                          'deliv_select_sphone_html5.tpl');

        foreach ($arrFiles as $file) {
            if (!copy(MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . '/plugin/' . $file,
                      $plugin_dir . $file)) {
                $this->arrErr['err'] = $plugin_dir . $file . ' のコピーに失敗しました';
                return false;
            }
        }

        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $exists = $objQuery->count('dtb_plugin', 'plugin_code = ?', array(MDL_PAYPAL_EXPRESS_CODE));
        if ($exists < 1) {
            $arrParams = array();
            $arrParams['plugin_id'] = $objQuery->nextVal('dtb_plugin_plugin_id');
            $arrParams['plugin_name'] = MDL_PAYPAL_EXPRESS_CODE;
            $arrParams['plugin_code'] = MDL_PAYPAL_EXPRESS_CODE;
            $arrParams['class_name'] = 'SC_Helper_Plugin_Paypal';
            $arrParams['plugin_description'] = '「PayPalでチェックアウト」ボタン表示用のプラグインです。このプラグインは、プラグイン管理画面から無効/削除できません。このプラグインを無効にしたい場合は、PayPal Express Checkout 決済モジュールの管理画面から「PayPalでチェックアウトボタンの使用」を無効にしてください。';
            $arrParams['enable'] = PLUGIN_ENABLE_TRUE;
            $arrParams['create_date'] = 'now()';
            $arrParams['update_date'] = 'now()';

            // 2.12系
            if (version_compare(ECCUBE_VERSION, '2.12.0', '>=')) {
                // do nothing
            // 2.12系
            } else {
                $arrParams['rank'] = $objQuery->max('rank', 'dtb_plugin') + 1;
                $arrParams['status'] = PLUGIN_STATUS_INSTALLED;
                $arrParams['del_flg'] = 0;
            }
            $objQuery->insert('dtb_plugin', $arrParams);
        }
        return true;
    }

    function registerPayPalAccessPlugin($arrParams) {
        return $this->installPlugin(self::PAYPAL_ACCESS_NAME, $arrParams);
    }

    function disablePayPalAccessPlugin() {
        return $this->disablePlugin(self::PAYPAL_ACCESS_NAME);
    }

    function registerDroppedItemsNoticerPlugin($arrParams) {
        $arrParams['free_field1'] = DROPPED_ITEMS_NOTICER_DEFAULT_SUBJECT;
        return $this->installPlugin(self::DROPPED_ITEMS_NOTICER_NAME, $arrParams);
    }

    function disableDroppedItemsNoticerPlugin() {
        return $this->disablePlugin(self::DROPPED_ITEMS_NOTICER_NAME);
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
     * 設定完了メールを送信します.
     */
    protected function sendSettingsCompleteMail() {
        $subject = '【日本発!ECオープンプラットフォーム EC-CUBE】モジュールダウンロードありがとうございます';
        $objMailView = new SC_SiteView_Ex();
        $body = $objMailView->fetch(MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . "/config_complete_mail.tpl");

        $arrSiteInfo = SC_Helper_DB_Ex::sfGetBasisData();

        $objMail = new SC_SendMail_Ex();
        $objMail->setItem(
            $arrSiteInfo['email01'], //
            $subject,
            $body,
            $arrSiteInfo['email01'],      // 送信元メールアドレス
            '',    // 送信元名
            $arrSiteInfo['email03'],      // reply_to
            $arrSiteInfo['email04'],      // return_path
            $arrSiteInfo['email04']       // errors_to
        );
        $objMail->sendMail();
    }

    /**
     * hookpoint を登録します.
     */
    protected function registerHookpoints($plugin_id, $arrHookPoints) {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        foreach ($arrHookPoints as $arrValues) {
            $arr_sqlval_plugin_hookpoint = array();
            $id = $objQuery->nextVal('dtb_plugin_hookpoint_plugin_hookpoint_id');
            $arr_sqlval_plugin_hookpoint['plugin_hookpoint_id'] = $id;
            $arr_sqlval_plugin_hookpoint['plugin_id'] = $plugin_id;
            $arr_sqlval_plugin_hookpoint['hook_point'] = $arrValues[0];
            $arr_sqlval_plugin_hookpoint['callback'] = $arrValues[1];
            $arr_sqlval_plugin_hookpoint['update_date'] = 'CURRENT_TIMESTAMP';
            $objQuery->insert('dtb_plugin_hookpoint', $arr_sqlval_plugin_hookpoint);
        }
    }
}
