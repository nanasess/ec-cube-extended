<?php
require_once(MODULE_REALDIR . "mdl_paypal_express/include.php");
require_once(MODULE_REALDIR . "mdl_paypal_express/LC_Page_Mdl_PaypalExpress_Config.php");
require_once(MODULE_REALDIR . "mdl_paypal_express/SC_Helper_Paypal.php");

class SC_Helper_Plugin_Paypal extends SC_Helper_Plugin_Ex {

    function preProcess($objPage){
        $class_name = get_class($objPage);
        switch ($class_name) {
            case 'LC_Page_Cart_Ex':
                // 必要に応じて前処理
                break;

        default:
        }
    }

    /**
     * カートページに 「PayPal でお支払い」ボタンを表示させる
     */
    function process($objPage) {
        // 2.12系は, isEnable()は呼び出されないため, ここで有効無効判定を行う.
        if (version_compare(ECCUBE_VERSION, '2.12.0', '>=')) {
            if (!SC_Helper_Paypal::useExpressBtn()) {
                return;
            }
        }
        $class_name = get_class($objPage);
        $masterData = new SC_DB_MasterData_Ex();
        $objPage->arrPref = $masterData->getMasterData('mtb_pref');

        switch ($class_name) {
            // お支払い方法選択にリンク付与
            case 'LC_Page_Shopping_Payment_Ex':
                $objPage->action();
                $objPage->include_mainpage = $objPage->tpl_mainpage;
                $objPage->tpl_mainpage = SC_Helper_Paypal::getPluginDir() . 'payment.tpl';
                break;

            case 'LC_Page_Cart_Ex':
                $objPage->action();

                $objCartSess = new SC_CartSession_Ex();
                // カートページを書きかえる
                $objPage->include_mainpage = $objPage->tpl_mainpage;
                $objPage->tpl_mainpage = SC_Helper_Paypal::getPluginDir() . 'cart.tpl';

                switch ($objPage->getMode()) {
                case 'do_express':
                    $objCustomer = new SC_Customer_Ex();

                    $objSiteSess = new SC_SiteSession_Ex();
                    $objPurchase = new SC_Helper_Purchase_Ex();

                    if (in_array($_POST['cartKey'], $objCartSess->getKeys())) {
                        $cartKey = $_POST['cartKey'];
                        $objPage->cartKey = $cartKey;
                    } else {
                        break;
                    }

                    $arrDelivSelect = SC_Helper_Paypal::getAvailableDeliv($cartKey);
                    // 配送業者未割り当ての場合はエラー表示
                    if (SC_Utils_Ex::isBlank($arrDelivSelect)) {
                        $objPage->tpl_message = '「PayPal でチェックアウト」ボタンは、現在ご利用になることができません。恐れ入りますがお問い合わせページよりお問い合わせください。';
                        break;
                    }
                    // 配送業者選択画面表示
                    elseif (SC_Utils_Ex::isBlank($_POST['deliv_id'])
                            && count($arrDelivSelect) > 1) {
                        $objPage->tpl_title = '配送方法の指定';
                        $objPage->tpl_mainpage = SC_Helper_Paypal::getDelivSelectTplPath();
                        $objPage->arrDelivSelect = $arrDelivSelect;
                        if ($_POST['retry'] == 1) {
                            $objPage->arrErr['deliv_id'] = '※ 配送業者を選択してください。';
                        }

                        break;
                    } else {
                        // 配送業者選択後
                        if (!SC_Utils_Ex::isBlank($_POST['deliv_id'])) {
                            $deliv_id = intval($_POST['deliv_id']);
                        }
                        // 配送業者が1件のみの場合
                        else {
                            $deliv_id = $arrDelivSelect[0]['deliv_id'];
                        }
                    }

                    // カート内集計
                    $arrParams = $objCartSess->calculate($cartKey, $objCustomer);

                    // カート を購入モードに設定
                    $cartList = $objCartSess->getCartList($cartKey);
                    if(count($cartList) > 0) {
                        $objPage->lfSetCurrentCart($objSiteSess, $objCartSess, $cartKey);
                    } else {
                        break;
                    }

                    // 一旦受注を完了させる
                    $objQuery =& SC_Query_Ex::getSingletonInstance();
                    $arrParams['order_id'] = $objQuery->nextval("dtb_order_order_id");
                    $_SESSION['order_id'] = $arrParams['order_id'];
                    $arrParams['deliv_id'] = $deliv_id;
                    $arrPayments = $objQuery->getRow('payment_id, charge', 'dtb_payment', 'memo03 = ?',
                                                     array(MDL_PAYPAL_EXPRESS_CODE));
                    $arrParams['payment_id'] = $arrPayments['payment_id'];
                    $arrParams['charge'] = $arrPayments['charge'];

                    $arrRequest = array();
                    // ログイン時は会員情報を受注データに反映
                    if ($objCustomer->isLoginSuccess()) {
                        $objPurchase->copyFromCustomer($arrParams, $objCustomer, 'shipping');
                        // ログインしていた場合はPayPal会員登録の初期値を設定
                        $arrRequest['ADDROVERRIDE'] = '1';
                        $arrRequest['PAYMENTREQUEST_0_SHIPTONAME'] = $objCustomer->getValue('name02') . ' ' . $objCustomer->getValue('name01');
                        $arrRequest['PAYMENTREQUEST_0_SHIPTOZIP'] = $objCustomer->getValue('zip01') . '-' . $objCustomer->getValue('zip02');
                        $arrRequest['PAYMENTREQUEST_0_SHIPTOSTATE'] = $objPage->arrPref[$objCustomer->getValue('pref')];
                        $arrRequest['PAYMENTREQUEST_0_SHIPTOCITY'] = $objCustomer->getValue('addr01');
                        $arrRequest['PAYMENTREQUEST_0_SHIPTOSTREET'] = $objCustomer->getValue('addr02');
                        $arrRequest['PAYMENTREQUEST_0_SHIPTOSTREET2'] = '';
                        $arrRequest['EMAIL'] = $objCustomer->getValue('email');
                        $arrRequest['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = $objCustomer->getValue('tel01') . '-'
                                                                         . $objCustomer->getValue('tel02') . '-'
                                                                         . $objCustomer->getValue('tel03');
                    }
                    // 非会員の場合はダミーデータを登録しておく
                    else {
                        $arrParams['order_name01'] = '(非会員 PayPal エクスプレスチェックアウト)';
                        $arrParams['customer_id'] = 0;
                        $arrParams['update_date'] = 'Now()';
                    }

                    $objPurchase->saveShippingTemp($arrParams);
                    $objPurchase->saveOrderTemp($objSiteSess->getUniqId(), $arrParams, $objCustomer);
                    $objSiteSess->setRegistFlag();
                    $objPurchase->completeOrder(ORDER_PENDING);

                    // PayPal API から token を取得
                    $arrRequest['PAYMENTREQUEST_0_SHIPPINGAMT'] = $arrParams['deliv_fee'];
                    $arrParams['payment_total'] += $arrParams['charge'];
                    $arrRequest['PAYMENTREQUEST_0_AMT'] = $arrParams['payment_total'];
                    $arrOrder = $objPurchase->getOrder($_SESSION['order_id']);
                    $arrDetails = $objPurchase->getOrderDetail($_SESSION['order_id'], false);
                    // ダウンロード商品の場合は配送先を表示しない
                    $hasDownload = SC_Helper_Paypal::hasDownload($_SESSION['order_id']);
                    if ($hasDownload) {
                        $arrRequest['NOSHIPPING'] = 1;
                    }
                    $arrRequest = array_merge($arrRequest, SC_Helper_Paypal::createItemRequests($arrDetails, $arrOrder));
                    $arrResponse = SC_Helper_Paypal::sendNVPRequest('SetExpressCheckout', $arrRequest, true);
                    if (SC_Helper_Paypal::isError($arrResponse)) {
                        $objPurchase->rollbackOrder($_SESSION['order_id'], ORDER_CANCEL, false);
                        $objPage->tpl_message = SC_Helper_Paypal::getErrorMessage($arrResponse);
                        break;
                    }
                    $_SESSION['token'] = $arrResponse['TOKEN'];

                    $link_url = SC_Helper_Paypal::getLinkURL();

                    header('Location: ' . $link_url . '&token=' . $_SESSION['token']);
                    exit;
                    break;
                default:
                }

                // 商品種別ごとの有効な配送業者の配列
                $arrDeliv = array();
                foreach ($objCartSess->getKeys() as $key) {
                    $arrDeliv[$key] = SC_Helper_Paypal::getAvailableDeliv($key);
                }
                $objPage->arrDeliv = SC_Utils_Ex::jsonEncode($arrDeliv);

                // PayPal チェックアウトボタン画像を取得
                $objPage->paypal_checkout_button_url = PAYPAL_EXPRESS_CHECKOUT_BUTTON;
                break;
        default:
        }
    }

    /**
     * 2.11系での有効無効判定
     *
     * @param string $class_name
     * @return integer|array
     */
    function isEnable($class_name) {
        if (SC_Helper_Paypal::useExpressBtn()) {
            $arrEnableClass = array('LC_Page_Cart_Ex');
            return in_array($class_name, $arrEnableClass);
        } else {
            return false;
        }
    }
}
?>
