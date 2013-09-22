<?php
require_once(realpath(dirname( __FILE__)) . '/define.php');
require_once(realpath(dirname( __FILE__)) . '/class/helper/SC_Helper_PayPalAccess.php');
/*
 * PayPalAccess
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
 * PayPalAccess プラグイン
 */
class PayPalAccess extends SC_Plugin_Base {

    /** 必須入力を解除する場合 */
    const REQUIRES_REVOKE_ENABLED = '1';
    /** 必須入力を解除しない場合 */
    const REQUIRES_REVOKE_DISABLED = '2';

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
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/copy/index.php", PLUGIN_HTML_REALDIR . $arrPlugin['plugin_code'] . "/index.php");
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/copy/jquery.paypalaccess_util.js", PLUGIN_HTML_REALDIR . $arrPlugin['plugin_code'] . "/jquery.paypalaccess_util.js");
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/copy/plg_paypalaccess.php", HTML_REALDIR . "frontparts/bloc/plg_paypalaccess.php");
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/templates/paypalaccess.tpl", TEMPLATE_REALDIR . 'frontparts/bloc/paypalaccess.tpl');
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/templates/paypalaccess.tpl", SMARTPHONE_TEMPLATE_REALDIR . 'frontparts/bloc/paypalaccess.tpl');

        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $arrTables = $objQuery->listTables();
        if (in_array(array('plg_paypalaccess_claims', 'plg_paypalaccess_token'), $arrTables)) {
            // テーブルが存在する場合はスキップ
            return;
        }

        $file = realpath(dirname( __FILE__)) . '/sql/create_table_' . DB_TYPE . '.sql';
        SC_Helper_PayPalAccess::executeSQL($file);
    }

    /**
     * 削除時に実行される処理を記述します.
     * @param array $arrPlugin dtb_pluginの情報配列
     * @return void
     */
    function uninstall($arrPlugin) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $file = realpath(dirname( __FILE__)) . '/sql/drop_table.sql';
        SC_Helper_PayPalAccess::executeSQL($file);
    }

    /**
     * 有効にした際に実行される処理を記述します.
     * @param array $arrPlugin dtb_pluginの情報配列
     * @return void
     */
    function enable($arrPlugin) {
        // エクスプレスチェックアウトのアップグレードを考慮して再度コピー
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/templates/paypalaccess.tpl",
             TEMPLATE_REALDIR . 'frontparts/bloc/paypalaccess.tpl');
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/templates/paypalaccess.tpl",
             SMARTPHONE_TEMPLATE_REALDIR . 'frontparts/bloc/paypalaccess.tpl');
        copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . "/copy/plg_paypalaccess.php",
             HTML_REALDIR . "frontparts/bloc/plg_paypalaccess.php");
        self::registerBloc($arrPlugin, DEVICE_TYPE_PC);
        self::registerBloc($arrPlugin, DEVICE_TYPE_SMARTPHONE);
    }

    /**
     * ブロックを登録する.
     */
    function registerBloc($arrPlugin, $device_type_id) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $arrValues['device_type_id'] = $device_type_id;
        $arrValues['bloc_id'] = 1 + $objQuery->max('bloc_id', 'dtb_bloc', 'device_type_id = ?',
                                                   array($arrValues['device_type_id']));
        $arrValues['bloc_name'] = 'PayPal アカウントでログインボタン';
        $arrValues['tpl_path'] = 'paypalaccess.tpl';
        $arrValues['filename'] = 'paypalaccess';
        $arrValues['php_path'] = 'frontparts/bloc/plg_paypalaccess.php';
        $arrValues['create_date'] = 'CURRENT_TIMESTAMP';
        $arrValues['update_date'] = 'CURRENT_TIMESTAMP';
        $arrValues['deletable_flg'] = '1';
        $arrValues['plugin_id'] = $arrPlugin['plugin_id'];
        $where = 'plugin_id = ? AND device_type_id = ?';
        $arrWhere = array($arrPlugin['plugin_id'], $device_type_id);
        if ($objQuery->exists('dtb_bloc', $where, $arrWhere)) {
            $objQuery->update('dtb_bloc', $arrValues, $where, $arrWhere);
        } else {
            $objQuery->insert('dtb_bloc', $arrValues);
        }
    }

    /**
     * 無効にした際に実行される処理を記述します.
     * @param array $arrPlugin dtb_pluginの情報配列
     * @return void
     */
    function disable($arrPlugin) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objQuery->delete('dtb_blocposition', 'bloc_id IN (SELECT bloc_id FROM dtb_bloc WHERE plugin_id = ?)', array($arrPlugin['plugin_id']));
        $objQuery->delete('dtb_bloc', 'plugin_id = ?', array($arrPlugin['plugin_id']));
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
        // SC_Helper_Transformのインスタンスを生成.
        $objTransform = new SC_Helper_Transform($source);
        if (!SC_Helper_PayPalAccess::loadConfig()) {
            $source = $objTransform->getHTML();
            return;
        }

        // 呼び出し元テンプレートを判定します.
        $template_dir = PLUGIN_UPLOAD_REALDIR . $this->arrSelfInfo['plugin_code'] . '/templates/';
        switch($objPage->arrPageLayout['device_type_id']){
            case DEVICE_TYPE_MOBILE: // モバイル
            case DEVICE_TYPE_SMARTPHONE: // スマホ
                // ログイン画面
                if (strpos($filename, 'mypage/login.tpl') !== false
                    || strpos($filename, 'shopping/index.tpl') !== false) {
                    $objTransform->select('h2.title')->insertAfter(file_get_contents($template_dir . 'login_btn_sphone.tpl'));
                } elseif (strpos($filename, 'mypage/change.tpl') !== false) {
                    $objTransform->select('section#mypagecolumn')->insertAfter(file_get_contents($template_dir . 'mypage_script_sphone.tpl'));
                }
                break;
            case DEVICE_TYPE_PC: // PC
                // ログイン画面
                if (strpos($filename, 'mypage/login.tpl') !== false
                    || strpos($filename, 'shopping/index.tpl') !== false) {
                    $objTransform->select('div#undercolumn')->insertAfter(file_get_contents($template_dir . 'login_btn.tpl'));
                } elseif (strpos($filename, 'mypage/change.tpl') !== false) {
                    $objTransform->select('div#mypagecolumn')->insertAfter(file_get_contents($template_dir . 'mypage_script.tpl'));
                }
                break;
            case DEVICE_TYPE_ADMIN: // 管理画面
            default:
                break;
        }

        // 変更を実行します
        $source = $objTransform->getHTML();
    }

    function loadClassFileChange(&$classname, &$classpath) {
        if (!SC_Helper_PayPalAccess::loadConfig()) {
            return;
        }

        if($classname == 'SC_Customer_Ex') {
            $classpath = PLUGIN_UPLOAD_REALDIR . PAYPAL_ACCESS_PLUGIN_NAME . "/class/SC_Customer_PayPalAccess.php";
            $classname = 'SC_Customer_PayPalAccess';
        }
        if ($classname == 'SC_Helper_Customer_Ex') {
            $classpath = PLUGIN_UPLOAD_REALDIR . PAYPAL_ACCESS_PLUGIN_NAME . "/class/helper/SC_Helper_Customer_PayPalAccess.php";
            $classname = 'SC_Helper_Customer_PayPalAccess';
        }
    }
}
