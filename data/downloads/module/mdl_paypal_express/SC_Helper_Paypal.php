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

require_once(realpath(dirname( __FILE__)) . "/include.php");
if (version_compare(ECCUBE_VERSION, '2.12', '>=')) {
    require_once(DATA_REALDIR . 'module/HTTP/Request.php');
} else {
    require_once(DATA_REALDIR . 'module/Request.php');
}

/**
 * PayPal Express Checkout のヘルパークラス
 *
 * @package Helper
 * @author LOCKON CO.,LTD.
 * @version $Id: SC_Helper_Paypal.php 1383 2013-06-24 08:21:00Z nanasess $
 */
class SC_Helper_Paypal {


    /**
     * 設定を保存
     *
     * @param array 管理画面の設定データ
     * @return void
     */
    function setConfig($arrConfig) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objQuery->update("dtb_module", array('sub_data' => serialize($arrConfig)),
                          "module_code = ?", array(MDL_PAYPAL_EXPRESS_CODE));
    }

    /**
     * 設定を取得
     *
     * @return array 管理画面の設定データの配列
     */
    function getConfig() {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $config = $objQuery->get("sub_data", "dtb_module", "module_code = ?", array(MDL_PAYPAL_EXPRESS_CODE));
        return unserialize($config);
    }

    /**
     * プラグインディレクトリを取得する
     *
     * - 2.12系 data/downloads/plugin/mdl_paypal_express
     * - 2.11系 data/plugin/mdl_paypal_express
     * 
     * return プラグインディレクトリ
     */
    function getPluginDir() {
        $plugin_dir = '';
        // 2.12系
        if (version_compare(ECCUBE_VERSION, '2.12.0', '>=')) {
            $plugin_dir = DATA_REALDIR . 'downloads/plugin/' . MDL_PAYPAL_EXPRESS_CODE . '/';
        // 2.11系
        } else {
            $plugin_dir = DATA_REALDIR . 'plugin/' . MDL_PAYPAL_EXPRESS_CODE . '/';
        }
        return $plugin_dir;
    }
    
    /**
     * サンドボックスを使用するかどうか.
     *
     * @return boolean サンドボックスを使用する場合 true
     */
    function useSandbox() {
        $arrConfig = SC_Helper_Paypal::getConfig();
        return $arrConfig['use_sandbox'] ? true : false;
    }

    /**
     * Express Checkout ボタンを使用するかどうか.
     *
     * @return boolean Express Checkout ボタンを使用する場合 true
     */
    function useExpressBtn() {
        $arrConfig = SC_Helper_Paypal::getConfig();
        return $arrConfig['use_express_btn'] ? true : false;
    }

    /**
     * 支払方法の更新処理（共通処理）
     *
     * @return void
     */
    function registerPayments() {
        $arrData['payment_method'] = "PayPal エクスプレスチェックアウト";
        if (version_compare(ECCUBE_VERSION, '2.13', '>=')) {
            $arrData['module_path'] = MDL_PAYPAL_EXPRESS_CODE . "/paypal_link.php";
        } else {
            $arrData['module_path'] = MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . "/paypal_link.php";
        }
        $arrData['charge_flg'] = "1";
        $arrData['fix'] = 3;
        $arrData['payment_image'] = 'paypal_payment_logo.gif';
        $arrData['creator_id'] = $_SESSION['member_id'];
        $arrData['create_date'] = "now()";
        $arrData['update_date'] = "now()";
        $arrData['memo03'] = MDL_PAYPAL_EXPRESS_CODE; // 決済モジュールを認識するためのデータ
        $arrData['del_flg'] = "0";

        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $exists = $objQuery->count('dtb_payment', 'memo03 = ?',
                                   array(MDL_PAYPAL_EXPRESS_CODE));
        // 支払方法データが存在すればUPDATE
        if ($exists > 0) {
            $objQuery->update("dtb_payment", $arrData, "memo03 = ?", array(MDL_PAYPAL_EXPRESS_CODE));
        }
        // 支払方法データが無ければINSERT
        else {
            // ランクの最大値を取得
            $max_rank = $objQuery->max('rank', 'dtb_payment');
            $arrData["rank"] = $max_rank + 1;
            $arrData['payment_id'] = $objQuery->nextVal('dtb_payment_payment_id');
            $objQuery->insert("dtb_payment", $arrData);
        }
    }

    /**
     * NVP API リクエスト送信
     *
     * PayPal NVP API のリクエストを送信し, 連想配列でレスポンスを返す.
     * リクエストが異常終了した場合は, エラーメッセージを返す.
     *
     * 以下のパラメータは, EC-CUBE 管理画面の設定内容等を使用し, 自動的に設定される.
     * 引数 $arrParams を使用して, 上書きすることも可能.
     * - USER
     * - PWD
     * - SIGNATURE
     * - VERSION
     * - PAYMENTREQUEST_0_PAYMENTACTION
     * - PAYMENTREQUEST_0_CURRENCYCODE
     * - RETURNURL
     * - CANCELURL
     * - PAYMENTREQUEST_0_INVNUM
     *
     * @param string $method NVP API メソッド
     * @param array $arrParams 追加のパラメータの連想配列
     * @param boolean do_express_checkout Express Checkout ボタンを使用した場合 true
     * @return array リクエストに成功した場合, NVP API のレスポンス; 失敗した場合, エラーメッセージ;
     */
    function sendNVPRequest($method, $arrParams = array(), $do_express_checkout = false) {
        $arrConfig = SC_Helper_Paypal::getConfig();
        $endpoint = '';
        if ($arrConfig['use_sandbox']) {
            $endpoint = PAYPAL_EXPRESS_NVP_URL_SANDBOX;
        } else {
            $endpoint = PAYPAL_EXPRESS_NVP_URL;
        }

        $req = new HTTP_Request($endpoint);
        $req->setMethod(HTTP_REQUEST_METHOD_POST);

        $arrRequests['USER'] = $arrConfig['api_user'];
        $arrRequests['PWD'] = $arrConfig['api_pass'];
        $arrRequests['SIGNATURE'] = $arrConfig['api_signature'];
        $arrRequests['VERSION'] = PAYPAL_EXPRESS_API_VERSION;
        $arrRequests['PAYMENTREQUEST_0_PAYMENTACTION'] = PAYPAL_EXPRESS_PAYMENTACTION;
        $arrRequests['PAYMENTREQUEST_0_CURRENCYCODE'] = PAYPAL_EXPRESS_CURRENCY_CODE;
        $arrRequests['RETURNURL'] = HTTPS_URL . 'shopping/load_payment_module.php?mode=express';
        if ($do_express_checkout) {
            $arrRequests['RETURNURL'] .= '&do_express_checkout=true';
        }

        $arrRequests['CANCELURL'] = HTTPS_URL . 'shopping/load_payment_module.php?mode=cancel';
        $arrRequests['PAYMENTREQUEST_0_INVNUM'] = $_SESSION['order_id'];
        $arrRequests['METHOD'] = $method;
        switch ($method) {
            case 'SetExpressCheckout':
                $arrRequests['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = PAYPAL_EXPRESS_COUNTRY_CODE;
                if (!SC_Utils_Ex::isBlank($arrConfig['corporate_logo'])) {
                    // XXX IMAGE_SAVE_URLPATH にも HTTPS_URL にも ROOT_URLPATH が含まれている
                    $arrRequests['LOGOIMG'] = HTTPS_URL . 'upload/save_image/' . $arrConfig['corporate_logo'];
                }
                if (!SC_Utils_Ex::isBlank($arrConfig['border_color'])) {
                    $arrRequests['CARTBORDERCOLOR'] = str_replace('#', '', $arrConfig['border_color']);
                }
                break;
            case 'DoExpressCheckoutPayment':
                if (SC_Utils_Ex::isBlank($_SESSION['BUTTONSOURCE'])) {
                    $arrRequests['BUTTONSOURCE'] = PAYPAL_EXPRESS_BUTTONSOURCE;
                } else {
                    $arrRequests['BUTTONSOURCE'] = $_SESSION['BUTTONSOURCE'];
                }
                break;
            default:
        }

        $arrRequests = array_merge($arrRequests, $arrParams);

        $logtext = "\n************ Start $method Request. ************";
        $logtext .= "\nEndPoint URL => " . $endpoint;
        foreach ($arrRequests as $key => $val) {
            $logtext .= "\n" . $key . " => " . $val;
        }
        $logtext .= "\n************ End $method Request. ************";
        GC_Utils::gfPrintLog($logtext);

        // 送信
        foreach ($arrRequests as $name => $val) {
            $req->addPostData($name, $val);
        }
        $response = $req->sendRequest();
        $req->clearPostData();

        // 通信エラーチェック
        if (!PEAR::isError($response)) {
            $body = $req->getResponseBody();
            $err_flg = false;
        } else {
            $mess = mb_convert_encoding($response->getMessage(), CHAR_CODE);
            $err_flg = true;
        }

        // レスポンス整理
        if (!$err_flg) {
            $arrResponse = array();
            $arrQueryStrings = explode('&', $body);
            foreach ($arrQueryStrings as $queryString) {
                $arrKVP = explode('=', $queryString);
                $arrResponse[$arrKVP[0]] = rawurldecode($arrKVP[1]);
            }
            $logtext = "\n************ Response from the start $method ************";
            foreach ($arrResponse as $key => $val) {
                $logtext .= "\n" . $key . " => " . $val;
            }
            $logtext .= "\n************ Response from the end $method ************";
            GC_Utils::gfPrintLog($logtext);
            GC_Utils::gfPrintLog(''); // ログ画面を整形するため改行させる
            return $arrResponse;
        } else {
            GC_Utils::gfPrintLog($mess);
            return $mess;
        }
    }

    /**
     * NVP API レスポンスを検証する.
     *
     * エラーの詳細については, $arrResponse の内容を参照すること.
     *
     * @param array $arrResponse NVP API レスポンスの配列
     * @return boolean Failure, FailureWithWarning, Warning の場合 true;
     *                 Success, SuccessWithWarning の場合 false
     */
    function isError($arrResponse) {

        switch ($arrResponse['ACK']) {
        case 'Success':
        case 'SuccessWithWarning':
            return false;
            break;

        case 'Failure':
        case 'FailureWithWarning':
        case 'Warning':
        default:
            return true;
        }
    }

    /**
     * NVP API のエラーメッセージを取得する.
     *
     * NVP API レスポンスから, 画面表示用のエラーメッセージを取得して返す.
     *
     * @param array $arrResponse NVP API レスポンスの配列
     * @return string 画面表示用のエラーメッセージ
     */
    function getErrorMessage($arrResponse) {
        $error = "";
        $i = 0;
        while ($arrResponse['L_ERRORCODE' . $i] != '') {
            $error .= $arrResponse['L_ERRORCODE' . $i] . ': '
                . $arrResponse['L_SHORTMESSAGE' . $i] . ' ';
            $i++;
        }
        return $error;
    }

    function isPdrError($arrResponse) {
        $i = 0;
        while ($arrResponse['L_ERRORCODE' . $i] != '') {
            if ($arrResponse['L_ERRORCODE' . $i] == '10486') {
                return true;
            }
            $i++;
        }
        return false;
    }

    /**
     * PayPal Express Checkout で使用するセッションをクリアする.
     *
     * @param boolean $clearOrderId 受注IDもクリアする場合 true
     * @return void
     */
    function clearSession($clearOrderId = true) {
        if ($clearOrderId) {
            unset($_SESSION['order_id']);
        }
        unset($_SESSION['PAYERID']);
        unset($_SESSION['token']);
        unset($_SESSION['deliv_id']);
    }

    /**
     * PayPal 決済が使用可能な配送業者を取得する.
     *
     * @param integer $product_type_id 商品種別ID
     * @return array 配送業者の配列
     */
    function getAvailableDeliv($product_type_id) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $col = 'T3.*';
        $from = <<< __EOS__
                dtb_payment T1
            LEFT JOIN dtb_payment_options T2
            ON T1.payment_id = T2.payment_id
            LEFT JOIN dtb_deliv T3
            ON T2.deliv_id = T3.deliv_id
__EOS__;
        $where = 'T1.memo03 = ? AND T3.product_type_id = ?';
        return $objQuery->select($col, $from, $where, array(MDL_PAYPAL_EXPRESS_CODE, $product_type_id));
    }

    /**
     * 受注詳細の配列から, 支払い詳細項目のリクエストを生成する.
     *
     * 受注の配列に, ポイント値引, 手数料, 値引きが存在する場合は, 支払い詳細項目に加える
     *
     * @param array $arrDetails 受注詳細の配列
     * @param array $arrOrder 受注情報の配列
     * @return array 支払い詳細項目のリクエスト
     */
    function createItemRequests($arrDetails, $arrOrder = array()) {
        $arrInfo = SC_Helper_DB_Ex::sfGetBasisData();
        $arrRequests = array();
        $i = 0;
        $total = 0;

        if ($arrOrder['charge'] > 0) {
            $arrDetails[] = array('product_code' => 'charge',
                                  'product_name' => '手数料',
                                  'quantity' => 1,
                                  'price' => $arrOrder['charge']);
        }

        if ($arrOrder['discount'] > 0) {
            $arrDetails[] = array('product_code' => 'discount',
                                  'product_name' => '値引き',
                                  'quantity' => 1,
                                  'price' => 0 - $arrOrder['discount']);
        }

        if ($arrOrder['use_point'] > 0) {
            $arrDetails[] = array('product_code' => 'use_point',
                                  'product_name' => 'ポイント値引き',
                                  'quantity' => 1,
                                  'price' => 0 - $arrOrder['use_point'] * POINT_VALUE);
        }

        foreach ($arrDetails as $detail) {
            $arrRequests['L_PAYMENTREQUEST_0_NUMBER' . $i] = $detail['product_code'];
            $name = $detail["product_name"];
            if (!SC_Utils_Ex::isBlank($detail["classcategory_name1"])) {
                $name .= "/". $detail["classcategory_name1"];
            }
            if (!SC_Utils_Ex::isBlank($detail["classcategory_name2"])) {
                $name .= "/" . $detail["classcategory_name2"];
            }
            $arrRequests['L_PAYMENTREQUEST_0_DESC' . $i] = $name;
            $arrRequests['L_PAYMENTREQUEST_0_QTY' . $i] = $detail['quantity'];
            // 手数料, 値引き, ポイント値引きは消費税加算を除外
            if ($detail['product_code'] == 'charge'
                || $detail['product_code'] == 'discount'
                || $detail['product_code'] == 'use_point') {
                $arrRequests['L_PAYMENTREQUEST_0_AMT' . $i] = $detail['price'];
            } else {
                if (version_compare(ECCUBE_VERSION, '2.13', '>=')) {
                    $arrRequests['L_PAYMENTREQUEST_0_AMT' . $i] = SC_Helper_TaxRule_Ex::sfCalcIncTax($detail["price"], $detail['product_id'], $detail['product_class_id']);
                } else {
                    $arrRequests['L_PAYMENTREQUEST_0_AMT' . $i] = SC_Utils_Ex::sfCalcIncTax($detail["price"], $arrInfo['tax'], $arrInfo['tax_rule']);
                }
            }
            $total += $arrRequests['L_PAYMENTREQUEST_0_AMT' . $i] * $detail['quantity'];
            $i++;
        }
        $arrRequests['PAYMENTREQUEST_0_ITEMAMT'] = $total;
        return $arrRequests;
    }

    /**
     * 都道府県名から都道府県IDを取得する.
     *
     * @param string $pref_name 都道府県名
     * @return integer 都道府県ID
     */
    function getPrefId($pref_name) {
        $arrPref = array('Hokkaido' => 1,
                         '北海道' => 1,
                         'Aomori' => 2,
                         '青森県' => 2,
                         'Iwate' => 3,
                         '岩手県' => 3,
                         'Miyagi' => 4,
                         '宮城県' => 4,
                         'Akita' => 5,
                         '秋田県' => 5,
                         'Yamagata' => 6,
                         '山形県' => 6,
                         'Fukushima' => 7,
                         '福島県' => 7,
                         'Ibaraki' => 8,
                         '茨城県' => 8,
                         'Tochigi' => 9,
                         '栃木県' => 9,
                         'Gunma' => 10,
                         '群馬県' => 10,
                         'Saitama' => 11,
                         '埼玉県' => 11,
                         'Chiba' => 12,
                         '千葉県' => 12,
                         'Tokyo' => 13,
                         '東京都' => 13,
                         'Kanagawa' => 14,
                         '神奈川県' => 14,
                         'Niigata' => 15,
                         '新潟県' => 15,
                         'Toyama' => 16,
                         '富山県' => 16,
                         'Ishikawa' => 17,
                         '石川県' => 17,
                         'Fukui' => 18,
                         '福井県' => 18,
                         'Yamanashi' => 19,
                         '山梨県' => 19,
                         'Nagano' => 20,
                         '長野県' => 20,
                         'Gifu' => 21,
                         '岐阜県' => 21,
                         'Shizuoka' => 22,
                         '静岡県' => 22,
                         'Aichi' => 23,
                         '愛知県' => 23,
                         'Mie' => 24,
                         '三重県' => 24,
                         'Shiga' => 25,
                         '滋賀県' => 25,
                         'Kyoto' => 26,
                         '京都府' => 26,
                         'Osaka' => 27,
                         '大阪府' => 27,
                         'Hyogo' => 28,
                         '兵庫県' => 28,
                         'Nara' => 29,
                         '奈良県' => 29,
                         'Wakayama' => 30,
                         '和歌山県' => 30,
                         'Tottori' => 31,
                         '鳥取県' => 31,
                         'Shimane' => 32,
                         '島根県' => 32,
                         'Okayama' => 33,
                         '岡山県' => 33,
                         'Hiroshima' => 34,
                         '広島県' => 34,
                         'Yamaguchi' => 35,
                         '山口県' => 35,
                         'Tokushima' => 36,
                         '徳島県' => 36,
                         'Kagawa' => 37,
                         '香川県' => 37,
                         'Ehime' => 38,
                         '愛媛県' => 38,
                         'Kochi' => 39,
                         '高知県' => 39,
                         'Fukuoka' => 40,
                         '福岡県' => 40,
                         'Saga' => 41,
                         '佐賀県' => 41,
                         'Nagasaki' => 42,
                         '長崎県' => 42,
                         'Kumamoto' => 43,
                         '熊本県' => 43,
                         'Oita' => 44,
                         '大分県' => 44,
                         'Miyazaki' => 45,
                         '宮崎県' => 45,
                         'Kagoshima' => 46,
                         '鹿児島県' => 46,
                         'Okinawa' => 47,
                         '沖縄県' => 47
                         );
        return $arrPref[$pref_name];
    }

    /**
     * PayPal 決済画面への URL を取得する.
     *
     * @return string PayPal 決済画面の URL
     */
    function getLinkURL() {
        if (SC_Helper_Paypal::useSandbox()) {
            return PAYPAL_EXPRESS_LINK_URL_SANDBOX;
        }
        return PAYPAL_EXPRESS_LINK_URL;
    }

    /**
     * 確認ページのテンプレートパスを返す.
     *
     * @return string 確認ページのテンプレートパス
     */
    function getConfirmTplPath() {
        switch (SC_Display_Ex::detectDevice()) {
        case DEVICE_TYPE_SMARTPHONE:
            if (version_compare(ECCUBE_VERSION, '2.11.2') >=0) {
                return MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . '/confirm_sphone_html5.tpl';
            } else {
                return MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . '/confirm_sphone.tpl';
            }
            break;

        case DEVICE_TYPE_PC:
        default:
        }
        return MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . '/confirm.tpl';
    }

    /**
     * エラーページのテンプレートパスを返す.
     *
     * @return string エラーページのテンプレートパス
     */
    function getErrorTplPath() {
        switch (SC_Display_Ex::detectDevice()) {
        case DEVICE_TYPE_SMARTPHONE:
            return MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . '/error_sphone.tpl';
            break;

        case DEVICE_TYPE_PC:
        default:
        }
        return MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . '/error.tpl';
    }

    /**
     * 配送業者選択画面のテンプレートパスを返す.
     *
     * @return string 配送業者選択画面のテンプレートパス
     */
    function getDelivSelectTplPath() {
        $plugin_dir = SC_Helper_Paypal::getPluginDir();
        switch (SC_Display_Ex::detectDevice()) {
        case DEVICE_TYPE_SMARTPHONE:
            if (version_compare(ECCUBE_VERSION, '2.11.2') >=0) {
                return $plugin_dir . 'deliv_select_sphone_html5.tpl';
            } else {
                return $plugin_dir . 'deliv_select_sphone.tpl';
            }
            break;

        case DEVICE_TYPE_PC:
        default:
        }
        return $plugin_dir . 'deliv_select.tpl';
    }

    /**
     * 受注がダウンロード商品を含むかどうか
     *
     * @param integer $order_id 受注ID
     * @return boolean ダウンロード商品を含む場合 true
     */
    function hasDownload($order_id) {
        $objPurchase = new SC_Helper_Purchase_Ex();
        $arrOrderDetails = $objPurchase->getOrderDetail($order_id, false);
        foreach ($arrOrderDetails as $detail) {
            if ($detail['product_type_id'] == PRODUCT_TYPE_DOWNLOAD) {
                return true;
            }
        }
        return false;
    }
}
?>