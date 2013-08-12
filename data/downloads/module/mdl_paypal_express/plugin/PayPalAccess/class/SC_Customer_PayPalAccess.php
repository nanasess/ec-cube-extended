<?php
require_once(CLASS_REALDIR . 'SC_Customer.php');
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/openid_connect/include.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/PayPalAccessClaims.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/PayPalAccessClient.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/helper/SC_Helper_PayPalAccess.php';

/**
 * PayPalAccess で使用する SC_Customer の拡張クラス.
 */
class SC_Customer_PayPalAccess extends SC_Customer {

    /**
     * ログインしているかどうかをチェックします.
     *
     * SC_Customer::isLoginSuccess() が true かつ, PayPal アカウントでログイン中の場合, 以下の機能を提供します.
     * - Id Token の有効期限チェック
     * - Id Token の有効期限が失効していた場合は, 更新
     * - Id Token の更新に失敗した場合は, 自動的にログアウト
     *
     * @return boolean ログイン中の場合 true
     */
    function isLoginSuccess($dont_check_email_mobile = false) {
        if (parent::isLoginSuccess($dont_check_email_mobile)) {
            $customer_id = $this->getValue('customer_id');
            $objClaims = SC_Helper_PayPalAccess::getClaimsByCustomer($customer_id);
            if (!is_object($objClaims)) {
                // PayPalアカウントでログインしていない
                return true;
            }

            // OpenID Connect の有効期限チェック
            if (SC_Helper_PayPalAccess::isExpire($objClaims->getUserId())) {
                if (!SC_Helper_PayPalAccess::refreshToken($objClaims->getUserId())) {
                    // IDトークンが更新できなかった場合はログアウトする
                    $this->EndSession();
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * ログアウトします.
     *
     * PayPal アカウントでログイン中の場合は, PayPal アカウントからもログアウトします.
     */
    function EndSession() {
        $customer_id = $this->getValue('customer_id');
        try {
            $objClaims = SC_Helper_PayPalAccess::getClaimsByCustomer($customer_id);
            if (is_object($objClaims)) {
                $objToken = SC_Helper_PayPalAccess::getToken($objClaims->getUserId());
                $arrConfig = SC_Helper_PayPalAccess::getConfig();
                $objClient = PayPalAccessClient::getInstance($arrConfig['app_id'], $arrConfig['app_secret']);
                $objClient->endSession($objToken->getIdToken());
            }
        } catch (OIDConnect_ClientException $e) {
            GC_Utils_Ex::gfPrintLog(print_r($e, true));
        }
        parent::EndSession();
    }
}