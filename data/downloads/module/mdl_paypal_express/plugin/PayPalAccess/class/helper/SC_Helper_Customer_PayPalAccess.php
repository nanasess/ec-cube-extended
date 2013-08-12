<?php
require_once(CLASS_REALDIR . 'helper/SC_Helper_Customer.php');
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/openid_connect/include.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/PayPalAccessClaims.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/PayPalAccessClient.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/helper/SC_Helper_PayPalAccess.php';

/**
 * PayPalAccess で使用する SC_Helper_Customer の拡張クラス.
 */
class SC_Helper_Customer_PayPalAccess extends SC_Helper_Customer {
    /**
     * 会員共通
     *
     * カナ(姓/名)の必須入力を制御します.
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     * @access public
     * @return void
     */
    function sfCustomerCommonParam(&$objFormParam) {
        $objFormParam->addParam('お名前(姓)', 'name01', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK' ,'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('お名前(名)', 'name02', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK' , 'MAX_LENGTH_CHECK'));
        $arrConfig = SC_Helper_PayPalAccess::getConfig();
        if ($arrConfig['requires_revoke'] == PayPalAccess::REQUIRES_REVOKE_ENABLED) {
            $objFormParam->addParam('お名前(フリガナ・姓)', 'kana01', STEXT_LEN, 'CKV', array('NO_SPTAB', 'SPTAB_CHECK' ,'MAX_LENGTH_CHECK', 'KANA_CHECK'));
            $objFormParam->addParam('お名前(フリガナ・名)', 'kana02', STEXT_LEN, 'CKV', array('NO_SPTAB', 'SPTAB_CHECK' ,'MAX_LENGTH_CHECK', 'KANA_CHECK'));
        } else {
            $objFormParam->addParam('お名前(フリガナ・姓)', 'kana01', STEXT_LEN, 'CKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK' ,'MAX_LENGTH_CHECK', 'KANA_CHECK'));
            $objFormParam->addParam('お名前(フリガナ・名)', 'kana02', STEXT_LEN, 'CKV', array('EXIST_CHECK', 'NO_SPTAB', 'SPTAB_CHECK' ,'MAX_LENGTH_CHECK', 'KANA_CHECK'));
        }
        $objFormParam->addParam('郵便番号1', 'zip01', ZIP01_LEN, 'n', array('EXIST_CHECK', 'SPTAB_CHECK' ,'NUM_CHECK', 'NUM_COUNT_CHECK'));
        $objFormParam->addParam('郵便番号2', 'zip02', ZIP02_LEN, 'n', array('EXIST_CHECK', 'SPTAB_CHECK' ,'NUM_CHECK', 'NUM_COUNT_CHECK'));
        $objFormParam->addParam('都道府県', 'pref', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam('住所1', 'addr01', MTEXT_LEN, 'aKV', array('EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('住所2', 'addr02', MTEXT_LEN, 'aKV', array('EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('お電話番号1', 'tel01', TEL_ITEM_LEN, 'n', array('EXIST_CHECK', 'SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('お電話番号2', 'tel02', TEL_ITEM_LEN, 'n', array('EXIST_CHECK', 'SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('お電話番号3', 'tel03', TEL_ITEM_LEN, 'n', array('EXIST_CHECK', 'SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('FAX番号1', 'fax01', TEL_ITEM_LEN, 'n', array('SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('FAX番号2', 'fax02', TEL_ITEM_LEN, 'n', array('SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('FAX番号3', 'fax03', TEL_ITEM_LEN, 'n', array('SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    }

    /**
     * 会員登録共通
     *
     * 性別の必須入力を制御します.
     *
     * @param SC_FormParam $objFormParam SC_FormParam インスタンス
     * @param boolean $isAdmin true:管理者画面 false:会員向け
     * @param boolean $is_mypage マイページの場合 true
     * @return void
     */
    function sfCustomerRegisterParam(&$objFormParam, $isAdmin = false, $is_mypage = false) {
        $objFormParam->addParam('パスワード', 'password', STEXT_LEN, 'a', array('EXIST_CHECK', 'SPTAB_CHECK', 'ALNUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('パスワード確認用の質問の答え', 'reminder_answer', STEXT_LEN, 'aKV', array('EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('パスワード確認用の質問', 'reminder', STEXT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $arrConfig = SC_Helper_PayPalAccess::getConfig();
        if ($arrConfig['requires_revoke'] == PayPalAccess::REQUIRES_REVOKE_ENABLED) {
            $objFormParam->addParam('性別', 'sex', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
        } else {
            $objFormParam->addParam('性別', 'sex', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        }
        $objFormParam->addParam('職業', 'job', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('年', 'year', 4, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'), '', false);
        $objFormParam->addParam('月', 'month', 2, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'), '', false);
        $objFormParam->addParam('日', 'day', 2, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'), '', false);
        $objFormParam->addParam('メールマガジン', 'mailmaga_flg', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));

        if (SC_Display_Ex::detectDevice() !== DEVICE_TYPE_MOBILE) {
            $objFormParam->addParam('メールアドレス', 'email', null, 'a', array('NO_SPTAB', 'EXIST_CHECK', 'EMAIL_CHECK', 'SPTAB_CHECK' ,'EMAIL_CHAR_CHECK'));
            $objFormParam->addParam('パスワード(確認)', 'password02', STEXT_LEN, 'a', array('EXIST_CHECK', 'SPTAB_CHECK' ,'ALNUM_CHECK'), '', false);
            if (!$isAdmin) {
                $objFormParam->addParam('メールアドレス(確認)', 'email02', null, 'a', array('NO_SPTAB', 'EXIST_CHECK', 'EMAIL_CHECK','SPTAB_CHECK' , 'EMAIL_CHAR_CHECK'), '', false);
            }
        } else {
            if (!$is_mypage) {
                $objFormParam->addParam('メールアドレス', 'email', null, 'a', array('EXIST_CHECK', 'EMAIL_CHECK', 'NO_SPTAB' ,'EMAIL_CHAR_CHECK', 'MOBILE_EMAIL_CHECK'));
            }
        }
    }
}