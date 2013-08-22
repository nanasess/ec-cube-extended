<?php
/**
 * PayPalAccess のヘルパークラス.
 */
class SC_Helper_PayPalAccess {
    const TOKEN_TABLE = 'plg_paypalaccess_token';
    const CLAIMS_TABLE = 'plg_paypalaccess_claims';
    const PLUGIN_TABLE = 'dtb_plugin';

    /**
     * Id Token を登録します.
     *
     * Id Token が既に存在する場合は更新します.
     *
     * @param OIDConnect_Token $objToken Id Token のオブジェクト
     * @return boolean 登録に成功した場合 true
     */
    public static function registerToken(OIDConnect_Token $objToken) {
        $arrToken = $objToken->toArray();
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrParams = $objQuery->extractOnlyColsOf(self::TOKEN_TABLE, $arrToken);
        $arrParams['update_date'] = 'CURRENT_TIMESTAMP';
        if (!self::existsToken($objToken->getUserId())) {
            $arrParams['create_date'] = 'CURRENT_TIMESTAMP';
            $objQuery->insert(self::TOKEN_TABLE, $arrParams);
        } else {
            unset($arrParams['create_date']);
            $objQuery->update(self::TOKEN_TABLE, $arrParams, 'user_id = ?',  array($objToken->getUserId()));
        }
        return true;
    }

    /**
     * クレームを登録します.
     *
     * クレームが既に存在する場合は更新します.
     *
     * @param PayPalAccessClaims $objClaims クレームのオブジェクト
     * @return boolean 登録に成功した場合 true
     */
    public static function registerClaims(PayPalAccessClaims $objClaims) {
        $arrClaims = $objClaims->toArray();
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrParams = $objQuery->extractOnlyColsOf(self::CLAIMS_TABLE, $arrClaims);
        $arrParams['update_date'] = 'CURRENT_TIMESTAMP';
        if (!self::existsClaims($objClaims->getUserId())) {
            $arrParams['create_date'] = 'CURRENT_TIMESTAMP';
            $objQuery->insert(self::CLAIMS_TABLE, $arrParams);
        } else {
            unset($arrParams['create_date']);
            $objQuery->update(self::CLAIMS_TABLE, $arrParams, 'sub = ?',  array($objClaims->getUserId()));
        }
        return true;
    }

    /**
     * クレームの情報を元に, EC-CUBE へ会員登録をします.
     *
     * 既に会員が存在する場合は, 会員情報を更新します.
     * 既存会員のメールアドレスと, クレーム情報のメールアドレスが異なる場合は, メールアドレスを更新しません.
     *
     * @param PayPalAccessClaims $objClaims クレームのオブジェクト
     * @return integer 顧客ID
     */
    public static function registerCustomer(PayPalAccessClaims $objClaims) {
        $arrCustomer = $objClaims->toCustomerArray();
        $arrExistCustomer = SC_Helper_Customer_Ex::sfGetCustomerDataFromId($objClaims->getCustomerId());

        // customer_id が取得できていない場合は, 同一のメールアドレスを検索
        if (SC_Utils_Ex::isBlank($arrExistCustomer)) {
            $arrExistCustomer = SC_Helper_Customer_Ex::sfGetCustomerDataFromId($objClaims->getCustomerId(), 'email = ? OR email_mobile = ?',
                                                                               array($objClaims->getEmail(), $objClaims->getEmail()));
            $objClaims->setCustomerId($arrExistCustomer['customer_id']);
        }

        if (SC_Utils_Ex::isBlank($arrExistCustomer['kana01'])) {
            $arrCustomer['kana01'] = ' ';
        }
        if (SC_Utils_Ex::isBlank($arrExistCustomer['kana02'])) {
            $arrCustomer['kana02'] = ' ';
        }

        /*
         * 顧客IDが登録済みの場合は, 既存のメールアドレスを引き継ぐ
         */
        if (!SC_Utils_Ex::isBlank($arrExistCustomer)
            && !SC_Utils_Ex::isBlank($arrExistCustomer['email'])
            && $objClaims->getEmail() != $arrExistCustomer['email']) {
            $arrCustomer['email'] = $arrExistCustomer['email'];
        } else {
            /*
             * 一旦退会 → 再入会で何回も welcome_point が付与されないように
             * 登録可能と判定された場合のみ welcome_point を付与する
             */
            $check_status = SC_Helper_Customer_Ex::sfCheckRegisterUserFromEmail($arrCustomer['email']);
            if ($check_status == '0') {
                $CONF = SC_Helper_DB_Ex::sfGetBasisData();
                $arrCustomer['point'] = $CONF['welcome_point'];
            }
        }
        $arrCustomer['customer_id'] = $objClaims->getCustomerId();
        $customer_id = SC_Helper_Customer_Ex::sfEditCustomerData($arrCustomer, $objClaims->getCustomerId());
        $objClaims->setCustomerId($customer_id);
        self::registerClaims($objClaims);
        return $customer_id;
    }

    /**
     * Id Token を更新する
     */
    public static function refreshToken($user_id) {
        $objToken = self::getToken($user_id);
        $arrConfig = self::getConfig();
        $objClient = PayPalAccessClient::getInstance($arrConfig['app_id'], $arrConfig['app_secret'], self::useSandbox());
        $objClient->setScopesArray(array(PayPalAccessScope::OPENID,
                                         PayPalAccessScope::PROFILE,
                                         PayPalAccessScope::ADDRESS,
                                         PayPalAccessScope::EMAIL,
                                         PayPalAccessScope::EXTENDED_SCOPE));
        try {
            $objRefreshToken = $objClient->refreshAccessToken($objToken->getRefreshToken());
            $objToken->setAccessToken($objRefreshToken->access_token);
            self::registerToken($objToken);
            GC_Utils_Ex::gfPrintLog('success refresh token.');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * アカウントのリンクを解除する.
     *
     * @param string $sub クレームの Subject
     * @return boolean アカウントのリンクを解除できた場合 true
     */
    public static function unlinkCustomer($sub) {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->query('UPDATE ' . self::CLAIMS_TABLE . ' SET customer_id = null WHERE sub = ?',array($sub));
        return true;
    }

    /**
     * トークンの存在をチェックします.
     *
     * @param string $user_id Id Token の user_id
     * @return boolean 既に同じトークンを保持している場合 true
     */
    public static function existsToken($user_id) {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $exists = $objQuery->get('user_id', self::TOKEN_TABLE, 'user_id = ?', array($user_id));
        return !SC_Utils_Ex::isBlank($exists);
    }

    /**
     * クレームの存在をチェックします.
     *
     * @param string クレームの Subject
     * @return boolean 既に同じ Subject のクレームを保持している場合 true
     */
    public static function existsClaims($sub) {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $exists = $objQuery->get('sub', self::CLAIMS_TABLE, 'sub = ?', array($sub));
        return !SC_Utils_Ex::isBlank($exists);
    }

    /**
     * クレームを取得します.
     *
     * @param string クレームの Subject
     * @return PayPalAccessClaims クレームのオブジェクト. クレームが存在しない場合は, 引数の Subject を格納した空のオブジェクトを返す.
     */
    public static function getClaims($sub) {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrClaims = $objQuery->getRow('*', self::CLAIMS_TABLE, 'sub = ?', array($sub));
        if (!SC_Utils_Ex::isBlank($arrClaims)) {
            return new PayPalAccessClaims($arrClaims);
        } else {
            $arrClaims['sub'] = $sub;
            return new PayPalAccessClaims($arrClaims);
        }
    }

    /**
     * トークンを取得します.
     *
     * @param string $user_id トークンの user_id
     * @return OIDConnect_Token トークンのオブジェクト. トークンが存在しない場合は null を返す.
     */
    public static function getToken($user_id) {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrToken = $objQuery->getRow('*', self::TOKEN_TABLE, 'user_id = ?', array($user_id));
        if (!SC_Utils_Ex::isBlank($arrToken)) {
            return new OIDConnect_Token($arrToken);
        } else {
            return null;
        }
    }

    /**
     * 顧客IDを元に, クレームを取得します.
     *
     * @param integer $customer_id 顧客ID
     * @return PayPalAccessClaims クレームのオブジェクト. クレームが存在しない場合は null を返す.
     */
    public static function getClaimsByCustomer($customer_id) {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrClaims = $objQuery->getRow('*', self::CLAIMS_TABLE, 'customer_id = ?', array($customer_id));
        if (!SC_Utils_Ex::isBlank($arrClaims)) {
            return new PayPalAccessClaims($arrClaims);
        }
        return null;
    }

    /**
     * クレームの情報を元に, ログインを実行する.
     *
     * @param PayPalAccessClaims クレームのオブジェクト.
     * @param string $email 他に Email の指定がある場合
     * @return boolean ログインに成功した場合 true
     */
    public static function doLogin($objClaims, $email = null) {
        $objCustomer = new SC_Customer();
        // XXX EC-CUBEからのみログアウトするため, _Ex は使用しない
        $objCustomer->EndSession();
        if (SC_Utils_Ex::isBlank($email)) {
            $email = $objClaims->getEmail();
        }
        $objCustomer->setLogin($email);
        return true;
    }

    /**
     * トークンの有効期限と妥当性をチェックする..
     *
     * @param string $user_id トークンの user_id
     * @return boolean 有効期限が切れていた場合 true
     */
    public static function isExpire($user_id) {
        $objToken = self::getToken($user_id);
        if ($objToken->isExpire()) {
            return true;
        } else {
            // 有効期限切れでなければ, トークンの妥当性チェック
            $arrConfig = self::getConfig();
            $objClient = PayPalAccessClient::getInstance($arrConfig['app_id'], $arrConfig['app_secret'], self::useSandbox());
            try {
                $id_token = $objToken->getIdToken();
                $verification = $objClient->validateToken($id_token);
                if ($user_id == $verification->user_id) {
                    return false;
                } else {
                    GC_Utils_Ex::gfPrintLog('Bad user_id!');
                    GC_Utils_Ex::gfPrintLog(print_r($verification, true));
                    return true;
                }
            } catch (OIDConnect_ClientException $e) {
                GC_Utils_Ex::gfPrintLog(print_r($e, true));
                // 何らかの例外が発生した場合は有効期限切れとみなす
                return true;
            }
        }
    }

    /**
     * プラグイン管理画面の設定を保存
     *
     * @param array 管理画面の設定データ
     * @return void
     */
    public static function setConfig($arrConfig) {
        $arrParams['free_field1'] = $arrConfig['app_id'];
        $arrParams['free_field2'] = $arrConfig['app_secret'];
        $arrParams['free_field3'] = $arrConfig['requires_revoke'];
        $arrParams['free_field4'] = ($arrConfig['use_sandbox'] == 1) ? 1 : '';
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objQuery->update(self::PLUGIN_TABLE, $arrParams,
                          "plugin_code = ?", array(PAYPAL_ACCESS_PLUGIN_NAME));
    }

    /**
     * プラグイン管理画面の設定を取得
     *
     * @return array 管理画面の設定データの配列
     */
    public static function getConfig() {
        static $arrConfig;
        if (SC_Utils_Ex::isBlank($arrConfig)) {
            $objQuery =& SC_Query_Ex::getSingletonInstance();
            $arrConfig = $objQuery->getRow("*", self::PLUGIN_TABLE, "plugin_code = ?",
                                           array(PAYPAL_ACCESS_PLUGIN_NAME));
            $arrConfig['app_id'] = $arrConfig['free_field1'];
            $arrConfig['app_secret'] = $arrConfig['free_field2'];
            $arrConfig['requires_revoke'] = $arrConfig['free_field3'];
            $arrConfig['use_sandbox'] = isset($arrConfig['free_field4']) ? $arrConfig['free_field4'] : false;
        }
        return $arrConfig;
    }

    /**
     * プラグインの設定をロードする.
     */
    public static function loadConfig() {
        $arrConfig = self::getConfig();
        if (is_null($arrConfig['app_id']) || is_null($arrConfig['app_secret'])) {
            return false;
        }
        return true;
    }

    /**
     * Sandbox を使用するかどうか.
     */
    public static function useSandbox() {
        $arrConfig = self::getConfig();
        return $arrConfig['use_sandbox'];
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
