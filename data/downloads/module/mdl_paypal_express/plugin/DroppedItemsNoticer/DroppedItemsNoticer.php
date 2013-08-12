<?php
require_once(realpath(dirname( __FILE__)) . '/define.php');
require_once(realpath(dirname( __FILE__)) . '/class/SC_CustomerList_Dropped.php');
require_once(realpath(dirname( __FILE__)) . '/class/helper/SC_Helper_DroppedItemsNoticer.php');
/*
 * DroppedItemsNoticer
 * Copyright (C) 2012 LOCKON CO.,LTD. All Rights Reserved.
 * http://www.lockon.co.jp/
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


/**
 * カゴ落ち通知メルマガ配信プラグイン
 */
class DroppedItemsNoticer extends SC_Plugin_Base {

    /**
     * コンストラクタ
     * プラグイン情報(dtb_plugin)をメンバ変数をセットします.
     * @param array $arrSelfInfo dtb_pluginの情報配列
     * @return void
     */
    public function __construct(array $arrSelfInfo) {
        parent::__construct($arrSelfInfo);
    }

    /**
     * インストール時に実行される処理を記述します.
     * @param array $arrPlugin dtb_pluginの情報配列
     * @return void
     */
    function install($arrPlugin) {
        // ロゴファイルをhtmlディレクトリにコピーします.
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/logo.png", PLUGIN_HTML_REALDIR . $arrPlugin['plugin_code'] . "/logo.png");
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/copy/dropped_items_template_input.php",
             HTML_REALDIR . ADMIN_DIR . "mail/dropped_items_template_input.php");
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/copy/" . PAYPAL_DROPPED_BATCH_FILENAME,
             USER_REALDIR . PAYPAL_DROPPED_BATCH_FILENAME);
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $arrTables = $objQuery->listTables();
        if (in_array(array('plg_droppeditemsnoticer_order', 'plg_droppeditemsnoticer_auth'), $arrTables)) {
            // テーブルが存在する場合はスキップ
            return;
        }

        $file = realpath(dirname( __FILE__)) . '/sql/create_table_' . DB_TYPE . '.sql';
        self::executeSQL($file);
        // 既存の受注に入れておく
        $arrOrder = $objQuery->select('order_id, create_date', 'dtb_order');
        foreach ($arrOrder as $order) {
            $objQuery->insert('plg_droppeditemsnoticer_order', array('order_id' => $order['order_id'],
                                                                     'complete_date' => $order['create_date']));
        }
    }

    /**
     * 削除時に実行される処理を記述します.
     * @param array $arrPlugin dtb_pluginの情報配列
     * @return void
     */
    function uninstall($arrPlugin) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $file = realpath(dirname( __FILE__)) . '/sql/drop_table.sql';
        self::executeSQL($file);
    }

    /**
     * 有効にした際に実行される処理を記述します.
     * @param array $arrPlugin dtb_pluginの情報配列
     * @return void
     */
    function enable($arrPlugin) {
    }

    /**
     * 無効にした際に実行される処理を記述します.
     * @param array $arrPlugin dtb_pluginの情報配列
     * @return void
     */
    function disable($arrPlugin) {
    }


    /**
     * prefilterコールバック関数
     * テンプレートの変更処理を行います.
     *
     * @param string &$source テンプレートのHTMLソース
     * @param LC_Page_Ex $objPage ページオブジェクト
     * @param string $filename テンプレートのファイル名
     * @return void
     */
    function prefilterTransform(&$source, LC_Page_Ex $objPage, $filename) {
        // // SC_Helper_Transformのインスタンスを生成.
        $objTransform = new SC_Helper_Transform($source);
        if (!SC_Helper_DroppedItemsNoticer::useNoticer()) {
            $source = $objTransform->getHTML();
            return;
        }
        // 呼び出し元テンプレートを判定します.
        $template_dir = PLUGIN_UPLOAD_REALDIR . $this->arrSelfInfo['plugin_code'] . '/templates/';
        switch($objPage->arrPageLayout['device_type_id']){
            case DEVICE_TYPE_MOBILE: // モバイル
            case DEVICE_TYPE_SMARTPHONE: // スマホ
                break;
            case DEVICE_TYPE_PC: // PC
                break;
            case DEVICE_TYPE_ADMIN: // 管理画面
            default:
                if (strpos($filename, 'customer/index.tpl') !== false
                    || strpos($filename, 'mail/index.tpl') !== false) {
                    $objTransform->select('#search_form>table')->appendChild(file_get_contents($template_dir . 'search_fields.tpl'));
                }
                else if (strpos($filename, 'customer/edit.tpl') !== false) {
                    $objTransform->select('#customer')->appendChild(file_get_contents($template_dir . 'dropped_items_results.tpl'));
                }

                if (strpos($filename, 'mail/subnavi.tpl') !== false) {
                    $objTransform->select('ul.level1')->appendChild(file_get_contents($template_dir . 'mail_subnavi.tpl'));
                }

                break;
        }

        // 変更を実行します
        $source = $objTransform->getHTML();
    }

    function loadClassFileChange(&$classname, &$classpath) {
    }

    /**
     * 受注一時情報を初期化する.
     *
     * LC_Page_Cart_action_after でコールします.
     */
    public function initOrderTemp(LC_Page_Ex $objPage) {
        $objSiteSess = new SC_SiteSession_Ex();
        $objCustomer = new SC_Customer_Ex();
        $uniqid = $objSiteSess->getUniqId();
        $arrValues['update_date'] = 'CURRENT_TIMESTAMP';
        if ($objCustomer->isLoginSuccess()) {
            $arrValues['customer_id'] = $objCustomer->getValue('customer_id');
        } else {
            $arrValues['customer_id'] = '0';
        }
        $objPurchase = new SC_Helper_Purchase_Ex();
        $objPurchase->saveOrderTemp($uniqid, $arrValues);
        // カートからランディングした情報をクリア
        if (isset($_SESSION['LANDING_CART'])) {
            $objPage->landing_cart = true;
            unset($_SESSION['LANDING_CART']);
        }
        GC_Utils_Ex::gfPrintLog('DroppedItemsNoticer - initialize dtb_order_temp : ' . $uniqid, DROPPED_ITEMS_NOTICER_LOG);
    }

    /**
     * コンバージョンをトラッキングする.
     *
     * LC_Page_Shopping_Complete_action_before でコールします.
     */
    public function trackConversion(LC_Page_Ex $objPage) {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $table = 'plg_droppeditemsnoticer_order';
        $where = 'order_id = ?';
        $order_id = $_SESSION['order_id'];
        if (SC_Utils_Ex::isBlank($order_id)) {
            GC_Utils_Ex::gfPrintLog('DroppedItemsNoticer - order_id is empty.', DROPPED_ITEMS_NOTICER_LOG);
            return;
        }
        if (!$objQuery->exists($table, $where, array($order_id))) {
            GC_Utils_Ex::gfPrintLog('DroppedItemsNoticer - tracking conversion: ' . $order_id, DROPPED_ITEMS_NOTICER_LOG);
            $objQuery->insert($table, array('order_id' => $order_id));
        } else {
            GC_Utils_Ex::gfPrintLog('DroppedItemsNoticer - skipping: ' . $order_id, DROPPED_ITEMS_NOTICER_LOG);
        }
    }

    /**
     * カゴ落ち会員の検索日付が入力されていた場合, extended_mode で検索する.
     *
     * LC_Page_Admin_Customer_action_before,
     * LC_Page_Admin_Mail_action_before でコールします.
     */
    public function searchDroppedItemsBeforeInvoke(LC_Page_Ex $objPage) {
        GC_Utils_Ex::gfPrintLog('DroppedItemsNoticer - before', DROPPED_ITEMS_NOTICER_LOG);
        foreach ($_POST as $key => $val) {
            if (strpos($key, 'search_dropped') !== false) {
                if (!SC_Utils_Ex::isBlank($val)) {
                    $_POST['extended_mode'] = 'dropped_items';
                    switch ($_POST['mode']) {
                        case 'csv':
                            $_POST['mode'] = 'dropped_items_csv';
                            break;
                        case 'input':
                            $_POST['mode'] = 'dropped_input';
                            break;
                        default:
                    }
                    break;
                }
            }
        }
    }

    /**
     * カゴ落ち顧客を検索します.
     *
     * LC_Page_Admin_Customer_action_after,
     * LC_Page_Admin_Mail_action_afterでコールします.
     */
    public function searchDroppedItemsAfterInvoke(LC_Page_Ex $objPage) {
        if ($_POST['extended_mode'] != 'dropped_items') {
            return;
        }
        $arrPlugin = SC_Plugin_Util_Ex::getPluginByPluginCode(DROPPED_ITEMS_NOTICER_PLUGIN_NAME);
        // extended_mode == 'dropped_items' の場合は, 検索結果を dispatch
        switch ($objPage->getMode()) {
            // 検索
            case 'search':
                $objFormParam = new SC_FormParam_Ex();
                SC_Helper_Customer_Ex::sfSetSearchParam($objFormParam);
                SC_Helper_DroppedItemsNoticer::addSearchParam($objFormParam);
                $objFormParam->setParam($_POST);
                $objFormParam->convParam();
                $objPage->arrForm = $objFormParam->getFormParamList();
                $objPage->arrHidden = $objFormParam->getSearchArray();
                $objPage->arrErr = SC_Helper_DroppedItemsNoticer::sfCheckErrorSearchParam($objFormParam);
                if ($objFormParam->getValue('search_mail_type') == '2'
                    || $objFormParam->getValue('search_mail_type') == '4') {
                    $objPage->arrErr['search_mail_type'] = 'カゴ落ち通知メルマガ配信は携帯メールアドレスには送信できません。';
                    $objPage->tpl_onload = 'alert("' . $objPage->arrErr['search_mail_type'] . '");';
                }
                if (SC_Utils_Ex::isBlank($objPage->arrErr)) {
                    GC_Utils_Ex::gfPrintLog('DroppedItemsNoticer - doSearch', DROPPED_ITEMS_NOTICER_LOG);
                    list($objPage->tpl_linemax, $objPage->arrData, $objPage->objNavi) = SC_Helper_DroppedItemsNoticer::sfGetSearchData($objFormParam->getHashArray());
                    $objPage->arrPagenavi = $objPage->objNavi->arrPagenavi;
                    $objPage->arrResults = $objPage->arrData;
                    $objPage->tpl_onload = <<< __EOF__
                                           $('.btn-normal').each(function() {
                                                   if ($(this).text() == '配信内容を設定する') {
                                                       $(this).text('カゴ落ち通知メルマガ配信内容を設定する');
                                                   }
                                           });
__EOF__;
                }
                break;

            // 配信内容設定
            case 'dropped_input':
                $objPage->tpl_mainpage = PLUGIN_UPLOAD_REALDIR . DROPPED_ITEMS_NOTICER_PLUGIN_NAME
                                         . '/templates/dropped_items_template_input_confirm.tpl';
                $objPage->tpl_subtitle = 'カゴ落ち通知メルマガテンプレート設定';
                $objFormParam = new SC_FormParam_Ex();
                SC_Helper_Customer_Ex::sfSetSearchParam($objFormParam);
                SC_Helper_DroppedItemsNoticer::addSearchParam($objFormParam);
                $objFormParam->setParam($_POST);
                $objFormParam->convParam();
                $objPage->arrForm = $objFormParam->getFormParamList();
                $objPage->arrHidden = $objFormParam->getSearchArray();

                $objPage->subject = $arrPlugin['free_field1'];
                $objPage->header = SC_Helper_DroppedItemsNoticer::readTemplate(DROPPED_ITEMS_NOTICER_HEADER);
                $objPage->footer =  SC_Helper_DroppedItemsNoticer::readTemplate(DROPPED_ITEMS_NOTICER_FOOTER);
                break;

            // 送信
            case 'dropped_query':
                define('WITH_PAYPAL_ADMIN', true);
                $objFormParam = new SC_FormParam_Ex();
                SC_Helper_Customer_Ex::sfSetSearchParam($objFormParam);
                SC_Helper_DroppedItemsNoticer::addSearchParam($objFormParam);
                $objFormParam->setParam($_POST);
                $objFormParam->convParam();
                $arrParams = $objFormParam->getHashArray();
                while(@ob_end_clean());
                ob_start();
                include(USER_REALDIR . PAYPAL_DROPPED_BATCH_FILENAME);
                ob_end_clean();
                echo SC_Utils_Ex::jsonEncode(array('result' => "カゴ落ち通知メルマガ配信が完了しました。"));
                exit; // XXX actionExit() したいが, $objPage が response を持っていない？
                break;

            // プレビュー
            case 'preview':
                $objProduct = new SC_Product_Ex();
                $arrSiteInfo = SC_Helper_DB_Ex::sfGetBasisData();
                $objQuery =& SC_Query_Ex::getSingletonInstance();
                $col = 'product_id, name, main_list_image, product_code_min AS product_code, price02_min AS price, main_list_comment, point_rate';
                $from = $objProduct->alldtlSQL();
                $objQuery->setLimit(3);
                $arrProducts = $objQuery->select($col, $from, $where, $arrValues);
                $arrDropped = array();
                foreach ($arrProducts as $key => $item) {
                    $arrProductsClass = $objProduct->getProductsClassByProductIds(array($item['product_id']));
                    $arrDropped[$key]['id'] = $arrProductsClass[0]['product_class_id'];
                    $arrDropped[$key]['quantity'] = 1;
                    $arrDropped[$key]['price'] = SC_Helper_DB::sfCalcIncTax($arrProductsClass[0]['price02']);
                    $arrDropped[$key]['point_rate'] = $arrProductsClass[0]['point_rate'];
                    $arrDropped[$key]['main_list_comment'] = $item['main_list_comment'];
                    $arrDropped[$key]['productsClass'] = $arrProductsClass[0];
                    $arrDropped[$key]['productsClass']['name'] = $item['name'];
                    $arrDropped[$key]['productsClass']['main_list_image'] = $item['main_list_image'];
                    $arrDropped[$key]['productsClass']['total_inctax'] = $arrDropped[$key]['price'];
                }
                $objMailView = new SC_SiteView_Ex();
                $objMailView->assignarray(array('arrCart' => $arrDropped,
                                                'arrSiteInfo' => $arrSiteInfo,
                                                'subject' => $arrPlugin['free_field1']));
                $objPage->mail['body'] = $objMailView->fetch(DROPPED_ITEMS_NOTICER_CONTENTS);
                $objPage->setTemplate('mail/preview.tpl');
                break;

            // カゴ落ち検索の場合のCSVダウンロード
            case 'dropped_items_csv':
                $this->downloadCSV($objPage);
                exit; // XXX actionExit() したいが, $objPage が response を持っていない？
                break;
            default:
        }
    }

    /**
     * カゴ落ち顧客をCSVダウンロードします.
     */
    function downloadCSV(LC_Page_Ex $objPage) {
        GC_Utils_Ex::gfPrintLog('DroppedItemsNoticer - do CSV download start', DROPPED_ITEMS_NOTICER_LOG);
        if ($_POST['extended_mode'] != 'dropped_items') {
            return;
        }

        $objFormParam = new SC_FormParam_Ex();
        SC_Helper_Customer_Ex::sfSetSearchParam($objFormParam);
        SC_Helper_DroppedItemsNoticer::addSearchParam($objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();
        $objPage->arrErr = SC_Helper_DroppedItemsNoticer::sfCheckErrorSearchParam($objFormParam);

        if (SC_Utils_Ex::isBlank($objPage->arrErr)) {
            GC_Utils_Ex::gfPrintLog('DroppedItemsNoticer - do CSV download', DROPPED_ITEMS_NOTICER_LOG);
            $objSelect = new SC_CustomerList_Dropped($objFormParam->getHashArray(), 'customer');
            $order = 'update_date DESC, customer_id DESC';
            $where = 'WHERE customer_id IN (' . $objSelect->getList() . ')';
            list($w, $arrVal) = $objSelect->getWhere();
            $objCSV = new SC_Helper_CSV_Ex();
            $objCSV->sfDownloadCsv('2', $where, $arrVal, $order, true);
        }
    }

    /**
     * カゴ落ち商品履歴一覧を表示します.
     *
     * LC_Page_Admin_Customer_Edit_action_after でコールします.
     */
    public function resultsDroppedItemsAfterInvoke(LC_Page_Ex $objPage) {
        $objPage->arrDroppedHistory = SC_Helper_DroppedItemsNoticer::findDroppedItemsHistories($objPage->arrForm['customer_id']);
        $objPage->tpl_dropped_linemax = count($objPage->arrDroppedHistory);
    }

    /**
     * メルマガからカートに商品を投入します.
     *
     * LC_Page_Products_List_action_before でコールします.
     */
    public function checkAuthDroppedItemsBeforeInvoke(LC_Page_Ex $objPage) {
        if (isset($_REQUEST['authcode'])) {
            $authcode = $_REQUEST['authcode'];
            if (preg_match('/[a-zA-Z0-9]+/', $authcode)) {
                $objQuery = SC_Query_Ex::getSingletonInstance();
                if (!$objQuery->exists('plg_droppeditemsnoticer_auth', 'authcode = ?',
                                       array($authcode))) {
                    GC_Utils_Ex::gfPrintLog('DroppedItemsNoticer -  authcode not found ' . print_r($_REQUEST, true), DROPPED_ITEMS_NOTICER_LOG);
                    $objPage->objDisplay->response->sendRedirect(HTTP_URL);
                    $objPage->objDisplay->response->actionExit();
                    return;
                }
            } else {
                GC_Utils_Ex::gfPrintLog('DroppedItemsNoticer - bad authcode ' . print_r($_REQUEST, true), DROPPED_ITEMS_NOTICER_LOG);
                // $objPage->objDisplay->response->sendRedirect(HTTP_URL);
                // $objPage->objDisplay->response->actionExit();
                return;
            }
        } else {
            return;
        }
        $product_id = intval($_REQUEST['product_id']);
        $product_class_id = intval($_REQUEST['product_class_id']);
        $quantity = intval($_REQUEST['quantity']);

        GC_Utils_Ex::gfPrintLog('DroppedItemsNoticer - add cart ' . print_r($_REQUEST, true), DROPPED_ITEMS_NOTICER_LOG);
        $objCartSess = new SC_CartSession_Ex();
        $objCartSess->addProduct($product_class_id, $quantity);
        $objCartSess->setValue('authcode', $authcode);
        $_SESSION['BUTTONSOURCE'] = DROPPED_ITEMS_BUTTONSOURCE;
        $_SESSION['LANDING_CART'] = true;
        $objPage->objDisplay->response->sendRedirect(CART_URLPATH);
        $objPage->objDisplay->response->actionExit();
    }

    /**
     * SQLファイルの SQL を実行する.
     *
     * @param string SQLファイルのパス
     * @return void
     * @throw Exception SQLファイルが見つからなかった場合
     */
    public static function executeSQL($file) {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        if (file_exists($file)) {
            $sqls = file_get_contents($file);
            $arrSql = explode(';', $sqls);
            foreach ($arrSql as $sql) {
                $sql = trim($sql);
                if (!SC_Utils_Ex::isBlank($sql)) {
                    $objQuery->query($sql);
                }
            }
        } else {
            throw new Exception('SQL file not found. =>' . $file);
        }
    }
}
