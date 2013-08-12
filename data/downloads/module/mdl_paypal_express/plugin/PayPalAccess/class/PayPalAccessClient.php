<?php
require_once(realpath(dirname( __FILE__)) . '/openid_connect/include.php');
require_once(realpath(dirname( __FILE__)) . '/PayPalAccessScope.php');

/**
 * PayPalAccess の OpenID Connect クライアント.
 */
class PayPalAccessClient extends OIDConnect_AbstractClient
implements OIDConnect_Client {

    const AUTHORIZATION = 'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize';
    const ACCESS_TOKEN = 'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/tokenservice';
    const PROFILE = 'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/userinfo';
    const LOGOUT = 'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/endsession';
    const VALIDATE = 'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/checkid';

    private static $instance;
    var $endpoints;
    var $key;
    var $secret;
    var $state;

    protected function __construct() {
    }

    /**
     * インスタンスを生成し, 取得する.
     *
     * @param string $key App ID
     * @param string $secret App Secret
     * @return PayPalAccessClient シングルトンのインスタンス.
     * @throw Exception $key または $secret が取得できなかった場合
     */
    public function getInstance($key, $secret) {
        if (is_null($key) || is_null($secret)) {
            throw new Exception('Can\'t get App ID and App Secret.');
        }

        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->setReturnUrl(HTTPS_URL . 'plugin/PayPalAccess/');
            self::$instance->setKey($key);
            self::$instance->setSecret($secret);
        }
        return self::$instance;
    }

    /**
     * Authorization フローを実行するための URL を取得します.
     *
     * あらかじめ self::setState() で state を設定しておく必要があります.
     */
    public function getAuthUrl() {
        return parent::getAuthUrl(self::AUTHORIZATION);
    }

    /**
     * Access Token を取得します.
     */
    public function getAccessToken($code) {
        return parent::getAccessToken($code, self::ACCESS_TOKEN);
    }

    /**
     * Access Token を更新します.
     */
    public function refreshAccessToken($refresh_token) {
        return parent::refreshAccessToken($refresh_token, self::ACCESS_TOKEN);
    }

    /**
     * Id Token を検証します.
     */
    public function validateToken($id_token) {
        return parent::validateToken($id_token, self::VALIDATE);
    }

    /**
     * プロフィールを取得します.
     */
    public function getProfile($access_token) {
        return parent::getProfile($access_token, self::PROFILE);
    }

    /**
     * ログアウトします.
     */
    public function endSession($id_token) {
        return parent::endSession($id_token, self::LOGOUT);
    }

    public function setKey($key) {
        $this->key = $key;
    }

    public function getKey() {
        return $this->key;
    }

    public function setSecret($secret) {
        $this->secret = $secret;
    }

    public function getSecret() {
        return $this->secret;
    }
}