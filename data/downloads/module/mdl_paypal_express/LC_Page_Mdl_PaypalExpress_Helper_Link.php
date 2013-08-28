<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
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
require_once(CLASS_EX_REALDIR . "page_extends/LC_Page_Ex.php");
require_once(realpath(dirname( __FILE__)) . "/include.php");
require_once(realpath(dirname( __FILE__)) . '/LC_Page_Mdl_PaypalExpress_Config.php');
require_once(realpath(dirname( __FILE__)) . "/SC_Helper_Paypal.php");
if (version_compare(ECCUBE_VERSION, '2.12', '>=')) {
    require_once(DATA_REALDIR . 'module/HTTP/Request.php');
} else {
    require_once(DATA_REALDIR . 'module/Request.php');
}

class LC_Page_Mdl_PaypalExpress_Helper_Link extends LC_Page_Ex {

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        $this->skip_load_page_layout = true;
        parent::init();
        $masterData = new SC_DB_MasterData();
        $this->arrPref = $masterData->getMasterData("mtb_pref");
        $this->arrDeliv = SC_Helper_DB_Ex::sfGetIDValueList("dtb_deliv", "deliv_id", "service_name");
        $this->tpl_title = 'PayPal でお支払い';
        $this->httpCacheControl('private');
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

        $arrConfig = SC_Helper_Paypal::getConfig();
        $link_url = PAYPAL_EXPRESS_LINK_URL;
        if (SC_Helper_Paypal::useSandbox()) {
            $link_url = PAYPAL_EXPRESS_LINK_URL_SANDBOX;
        }

        $objPurchase = new SC_Helper_Purchase_Ex();
        $arrOrder = $objPurchase->getOrder($_SESSION['order_id']);
        $arrOrder = array_merge($arrOrder, $arrConfig);

        $objFormParam = new SC_FormParam();
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();

        switch($this->getMode()) {

        // PayPal から遷移し, 確認画面を表示
        case 'express':
            $arrResponse = SC_Helper_Paypal::sendNVPRequest('GetExpressCheckoutDetails', array('TOKEN' => $_SESSION['token']));
            if (SC_Helper_Paypal::isError($arrResponse)) {
                $objPurchase->rollbackOrder($_SESSION['order_id'], ORDER_CANCEL, false);
                $this->tpl_message = SC_Helper_Paypal::getErrorMessage($arrResponse);
                $this->tpl_mainpage = SC_Helper_Paypal::getErrorTplPath();
                break;
            }
            $_SESSION['PAYERID'] = $arrResponse['PAYERID'];

            // Express Checkout ボタンを使用した場合
            if ($_GET['do_express_checkout']) {
                $arrShipping = $this->getShippings($arrResponse);
                $arrShipping['deliv_id'] = $arrOrder['deliv_id'];

                $arrOrder = $this->calculateOrder($_SESSION['order_id'], $arrOrder, $arrShipping['shipping_pref'], $arrShipping['deliv_id']);
                $objCustomer = new SC_Customer_Ex();

                // 非ログイン時は購入者の情報も取得
                if (!$objCustomer->isLoginSuccess()) {
                    $arrOrder['order_name01'] = $arrResponse['LASTNAME'];
                    $arrOrder['order_name02'] = $arrResponse['FIRSTNAME'];
                    $arrOrder['order_email'] = $arrResponse['EMAIL'];

                    // カナは取得できないのでダミーデータを入れる
                    $arrOrder['order_kana01'] = 'ー';
                    $arrOrder['order_kana02'] = 'ー';

                    // 電話番号を4文字づつに分割
                    if (!SC_Utils_Ex::isBlank($arrResponse['PHONENUM'])) {
                        $tel = preg_replace('/[^0-9]/', '', $arrResponse['PHONENUM']);
                        $arrTel = str_split($tel, 4);
                        $i = 1;
                        $arrOrder['order_tel03'] = '';
                        foreach ($arrTel as $num) {
                            if ($i <= 2) {
                                $arrOrder['order_tel0' . $i] = $num;
                            } else {
                                $arrOrder['order_tel03'] .= $num;
                            }
                            $i++;
                        }
                    }

                    if (!SC_Utils_Ex::isBlank($arrResponse['PAYMENTREQUEST_0_NOTETEXT'])) {
                        $arrOrder['message'] = $arrResponse['PAYMENTREQUEST_0_NOTETEXT'];
                    }
                }

                $objPurchase->registerOrder($_SESSION['order_id'], $arrOrder);
                $objPurchase->registerShipping($_SESSION['order_id'], array($arrShipping));

                $this->arrOrder = $arrOrder;
                $this->arrOrderDetails = $objPurchase->getOrderDetail($_SESSION['order_id'], false);
                $objProduct = new SC_Product_Ex();
                foreach (array_keys($this->arrOrderDetails) as $key) {
                    $this->arrOrderDetails[$key]['productsClass'] =& $objProduct->getDetailAndProductsClass($this->arrOrderDetails[$key]['product_class_id']);
                }
                $this->arrShipping = $arrShipping;
                $this->has_download = SC_Helper_PayPal::hasDownload($_SESSION['order_id']);
                $this->tpl_mainpage = SC_Helper_Paypal::getConfirmTplPath();
                break;
            }

        // 購入完了
        case 'confirm':
            $arrRequest['PAYERID'] = $_SESSION['PAYERID'];
            $arrRequest['PAYMENTREQUEST_0_AMT'] = $arrOrder['payment_total'];
            $arrRequest['TOKEN'] = $_SESSION['token'];
            $arrResponse = SC_Helper_Paypal::sendNVPRequest('DoExpressCheckoutPayment', $arrRequest);
            if (SC_Helper_Paypal::isError($arrResponse)) {
                // PDR 実行(ペイパル画面でエラーを表示する)
                if (SC_Helper_Paypal::isPdrError($arrResponse)) {
                    $_SESSION['token'] = $arrResponse['TOKEN'];
                    header('Location: ' . $link_url . '&token=' . $_SESSION['token']);
                    exit;
                } else {
                    $objPurchase->rollbackOrder($_SESSION['order_id'], ORDER_CANCEL, false);
                    $this->tpl_message = SC_Helper_Paypal::getErrorMessage($arrResponse);
                    $this->tpl_mainpage = SC_Helper_Paypal::getErrorTplPath();
                }
                break;
            }
            $objPurchase->sfUpdateOrderStatus($_SESSION['order_id'], ORDER_PRE_END);
            $objPurchase->sendOrderMail($_SESSION['order_id']);
            SC_Helper_Paypal::clearSession(false);
            unset($_SESSION['BUTTONSOURCE']);
            SC_Response_Ex::sendRedirect(SHOPPING_COMPLETE_URLPATH);
            exit;
            break;

        // 前のページに戻る
        case 'cancel':
        case 'return':
            // 受注削除してカートに戻す処理
            $objPurchase->rollbackOrder($_SESSION['order_id'], ORDER_CANCEL, true);
            SC_Helper_Paypal::clearSession();
            SC_Response_Ex::sendRedirect(CART_URLPATH);
            exit;
            break;

        default:
            // モバイルの場合はエラー画面を表示
            if (SC_Display_Ex::detectDevice() == DEVICE_TYPE_MOBILE) {
                $tpl_mainpage = MODULE_REALDIR . MDL_PAYPAL_EXPRESS_CODE . '/paypal_link_mobile.tpl';
                break;
            }

            $arrRequest['NOSHIPPING'] = 1;
            $arrRequest['PAYMENTREQUEST_0_SHIPPINGAMT'] = $arrOrder['deliv_fee'];
            $arrRequest['PAYMENTREQUEST_0_AMT'] = $arrOrder['payment_total'];
            $arrDetails = $objPurchase->getOrderDetail($_SESSION['order_id'], false);
            $arrRequest = array_merge($arrRequest, SC_Helper_Paypal::createItemRequests($arrDetails, $arrOrder));
            $arrResponse = SC_Helper_Paypal::sendNVPRequest('SetExpressCheckout', $arrRequest);
            if (SC_Helper_Paypal::isError($arrResponse)) {
                $objPurchase->rollbackOrder($_SESSION['order_id'], ORDER_CANCEL, false);
                $this->tpl_message = SC_Helper_Paypal::getErrorMessage($arrResponse);
                $this->tpl_mainpage = SC_Helper_Paypal::getErrorTplPath();
                break;
            }

            $_SESSION['token'] = $arrResponse['TOKEN'];
            header('Location: ' . $link_url . '&token=' . $_SESSION['token']);
            exit;
            break;
        }

        $this->arrForm = $objFormParam->getFormParamList();
    }

    function destroy() {
        parent::destroy();
    }

    /**
     * NVP API のレスポンスから配送情報を取得する.
     *
     * @param array $arrResponse NVP API のレスポンス情報
     * @return array 配送情報の配列
     */
    function getShippings($arrResponse) {
        $arrShipping['shipping_name01'] = $arrResponse['PAYMENTREQUEST_0_SHIPTONAME'];
        $arrShipping['shipping_addr02'] = $arrResponse['PAYMENTREQUEST_0_SHIPTOSTREET'] . ' ' . $arrResponse['PAYMENTREQUEST_0_SHIPTOSTREET2'];
        $arrShipping['shipping_addr01'] = $arrResponse['PAYMENTREQUEST_0_SHIPTOCITY'];
        $arrShipping['shipping_pref'] = SC_Helper_Paypal::getPrefId($arrResponse['PAYMENTREQUEST_0_SHIPTOSTATE']);
        $arrZip = explode('-', $arrResponse['PAYMENTREQUEST_0_SHIPTOZIP']);
        $arrShipping['shipping_zip01'] = $arrZip[0];
        $arrShipping['shipping_zip02'] = $arrZip[1];
        return $arrShipping;
    }

    /**
     * 受注の集計を行なう.
     *
     * @param integer $order_id 受注ID
     * @param array $arrOrder 受注情報の配列
     * @param integer $pref_id 都道府県ID
     * @param integer $deliv_id 配送業者ID
     * @return array 受注情報の配列
     */
    function calculateOrder($order_id, $arrOrder, $pref_id, $deliv_id) {
        // 配送業者の送料を加算
        if (OPTION_DELIV_FEE == 1) {
            // 2.13系はSC_Helper_Delivery_Exを使用する

            if (version_compare(ECCUBE_VERSION, '2.13', '>=')) {
                $arrOrder['deliv_fee'] += SC_Helper_Delivery_Ex::getDelivFee($pref_id, $deliv_id);
            }
            // 2.12系はSC_CartSession_Exを使用する
            else if (version_compare(ECCUBE_VERSION, '2.12.0', '>=')
                       && version_compare(ECCUBE_VERSION, '2.13', '<')) {
                $arrOrder['deliv_fee'] += SC_CartSession_Ex::sfGetDelivFee($pref_id, $deliv_id);
            } else {
                $arrOrder['deliv_fee'] += SC_Helper_DB_Ex::sfGetDelivFee($pref_id, $deliv_id);
            }
        }

        // 送料無料の購入数が設定されている場合
        if (DELIV_FREE_AMOUNT > 0) {
            $objPurchase = new SC_Helper_Purchase_Ex();
            $arrDetails = $objPurchase->getOrderDetail($order_id, false);
            if (count($arrDetails) >= DELIV_FREE_AMOUNT) {
                $arrOrder['deliv_fee'] = 0;
            }
        }

        // 送料無料条件が設定されている場合
        $arrInfo = SC_Helper_DB_Ex::sfGetBasisData();
        if ($arrInfo['free_rule'] > 0) {
            // 小計が無料条件を超えている場合
            if($arrOrder['subtotal'] >= $arrInfo['free_rule']) {
                $arrOrder['deliv_fee'] = 0;
            }
        }

        // 合計を計算
        $arrOrder['total'] = $arrOrder['subtotal'];
        $arrOrder['total'] += $arrOrder['deliv_fee'];
        $arrOrder['total'] += $arrOrder['charge'];
        $arrOrder['total'] -= $arrOrder['discount'];

        // お支払い合計
        $arrOrder['payment_total'] = $arrOrder['total'];
        return $arrOrder;
    }
}
?>
