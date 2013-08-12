<?php

/**
 * OpenID Connect の Id Token オブジェクト
 */
class OIDConnect_Token {

    var $user_id;
    var $token_type;
    var $id_token;
    var $expires_in;
    var $access_token;
    var $refresh_token;
    var $create_date;
    var $update_date;

    public function __construct($arrToken = array()) {
        if (is_array($arrToken)) {
            $this->setPropertiesFromArray($arrToken);
        }
    }

    /**
     * Access Token Endpoint から取得したトークンからインスタンスを生成します.
     *
     * @param StdClass $objRawToken Access Token Endpoint から取得したトークン
     * @return new instance.
     */
    public static function createInstance($objRawToken) {
        $instance = new self();
        $instance->copyFrom($objRawToken);
        $objJwt = $instance->getIdTokenAsObject();
        $instance->setUserId($objJwt->user_id);
        return $instance;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    public function getTokenType() {
        return $this->token_type;
    }

    public function setTokenType($token_type) {
        $this->token_type = $token_type;
    }

    public function setIdToken($id_token) {
        $this->id_token = $id_token;
    }

    public function getIdToken() {
        return $this->id_token;
    }

    /**
     * ID Token の JWT オブジェクトを返します.
     *
     * 本来なら PEAR::JWT を使用したいが, json_* 関数に依存しているため, 独自実装
     * @see https://github.com/luciferous/jwt
     */
    public function getIdTokenAsObject() {
        $tks = explode('.', $this->id_token);
        list($head, $payload, $crypt) = $tks;
        return SC_Utils_Ex::jsonDecode(base64_decode($payload));
    }

    public function setExpiresIn($expires_in) {
        $this->expires_in = $expires_in;
    }

    public function getExpiresIn() {
        return $this->expires_in;
    }

    public function isExpire() {
        $expire = time() + $this->getExpiresIn();
        return $expire < $this->getUpdateDateToTime();
    }

    public function setAccessToken($access_token) {
        $this->access_token = $access_token;
    }

    public function getAccessToken() {
        return $this->access_token;
    }

    public function setRefreshToken($refresh_token) {
        $this->refresh_token = $refresh_token;
    }

    public function getRefreshToken() {
        return $this->refresh_token;
    }

    public function getCreateDate() {
        return $this->create_date;
    }

    public function getUpdateDate() {
        return $this->update_date;
    }

    public function getUpdateDateToTime() {
        return strtotime($this->getUpdateDate());
    }

    /**
     * 引数の連想配列からプロパティを設定します.
     */
    public function setPropertiesFromArray($arrProperties) {
        foreach ($arrProperties as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }

    /**
     * プロパティの情報を連想配列で返します.
     */
    public function toArray() {
        $objReflect = new ReflectionClass($this);
        $arrProperties = $objReflect->getProperties();
        $arrResults = array();
        foreach ($arrProperties as $objProperty) {
            // $objProperty->setAccessible(true); XXX PHP5.3.2 未満未対応...
            $name = $objProperty->getName();
            $arrResults[$name] = $objProperty->getValue($this);
        }
        return $arrResults;
    }

    /**
     * Token endpoint から取得し, JSON decode した結果を自分自身に設定します.
     */
    public function copyFrom($objRawToken) {
        $this->token_type = $objRawToken->token_type;
        $this->id_token = $objRawToken->id_token;
        $this->expires_in = $objRawToken->expires_in;
        $this->access_token = $objRawToken->access_token;
        $this->refresh_token = $objRawToken->refresh_token;
    }
}
