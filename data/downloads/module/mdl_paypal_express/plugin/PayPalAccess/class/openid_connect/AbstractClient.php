<?php
require_once(realpath(dirname( __FILE__)) . '/include.php');

/**
 * OpenID Connect クライアントの抽象クラス.
 */
abstract class OIDConnect_AbstractClient {

    var $client_id;
    var $nonce;
    var $state;
    var $return_url;
    var $access_token;
    var $refresh_token;
    var $id_token;
    var $scopes;

    /**
     * Authorization endpoint からURLを生成します.
     */
    public function getAuthUrl($authorization_endopoint) {
        $this->setNonce(time().rand());
        $auth_url = sprintf("%s?client_id=%s&response_type=code&scope=%s&redirect_uri=%s&nonce=%s&state=%s",
                            $authorization_endopoint,
                            $this->getKey(),
                            $this->getScopesAsEncoded(),
                            $this->getReturnUrlAsEncoded(),
                            $this->getNonce(),
                            $this->getState());

        return $auth_url;
    }

    /**
     * Access Token を取得します.
     */
    public function getAccessToken($code, $accesstoken_endpoint) {
        $arrParams['client_id'] = $this->getKey();
        $arrParams['client_secret'] = $this->getSecret();
        $arrParams['grant_type'] = 'authorization_code';
        $arrParams['code'] = rawurlencode($code);
        $objToken = $this->post($accesstoken_endpoint, $arrParams);
        return $objToken;
    }

    /**
     * Access Token を更新します.
     */
    public function refreshAccessToken($refresh_token, $refreshtoken_endpoint) {
        $arrParams['client_id'] = $this->getKey();
        $arrParams['client_secret'] = $this->getSecret();
        $arrParams['grant_type'] = 'refresh_token';
        $arrParams['scope'] = $this->getScopesAsEncoded();
        $arrParams['refresh_token'] = $refresh_token;
        $objToken = $this->post($refreshtoken_endpoint, $arrParams);
        return $objToken;
    }

    /**
     * Id Token を検証します.
     */
    public function validateToken($id_token, $validate_endpoint) {
        return $this->post($validate_endpoint, array('access_token' => $id_token));
    }

    /**
     * Access Token を使用して, Profile を取得します.
     */
    public function getProfile($access_token, $userinfo_endpoint) {
        $profile_url = sprintf("%s?schema=openid&access_token=%s",
                               $userinfo_endpoint,
                               $access_token);
        $profile = $this->get($profile_url);
        return $profile;
    }

    /**
     * ログアウトします.
     */
    public function endSession($id_token, $logout_endpoint) {
        $logout_url = sprintf("%s?id_token=%s&redirect_uri=%s",
                              $logout_endpoint,
                              $id_token,
                              $this->getReturnUrlAsEncoded() . "&logout=true");
        return $this->get($logout_url);
    }

    public function setClientId($client_id) {
        $this->client_id = $client_id;
    }

    public function getClientId() {
        return $this->client_id;
    }

    public function setNonce($nonce) {
        $this->nonce = $nonce;
    }

    public function getNonce() {
        return $this->nonce;
    }

    public function setState($state) {
        $this->state = $state;
    }

    public function getState() {
        return $this->state;
    }

    public function setReturnUrl($return_url) {
        $this->return_url = $return_url;
    }

    public function getReturnUrl() {
        return $this->return_url;
    }

    public function setAccessToken($access_token) {
        $this->access_token = $access_token;
    }


    public function setRefreshToken($refresh_token) {
        $this->refresh_token = $refresh_token;
    }

    public function getRefreshToken() {
        return $this->refresh_token;
    }

    public function setIdToken($id_token) {
        $this->id_token = $id_token;
    }

    public function getIdToken() {
        return $this->id_token;
    }

    public function setScopes($scopes) {
        $this->scopes = $scopes;
    }

    public function getScopes() {
        return $this->scopes;
    }

    /**
     * Scope を配列から設定します.
     */
    public function setScopesArray($arrScopes) {
        $this->setScopes(implode(' ', $arrScopes));
    }

    /**
     * URLEncode された Scope を返します.
     */
    public function getScopesAsEncoded() {
        return urlencode($this->getScopes());
    }

    /**
     * URLEncode された Return URL を返します.
     */
    public function getReturnUrlAsEncoded() {
        return urlencode($this->getReturnUrl());
    }

    /**
     * POST リクエストを送信し, JSON 形式の結果をデコードします.
     *
     * @param string $url URL
     * @param array $arrParams POST リクエストのパラメータ
     * @return mixed JSON 形式の結果をデコードしたデータ.
     */
    public function post($url, $arrParams) {
        $objReq = new HTTP_Request();
        $objReq->setUrl($url);
        $objReq->setMethod('POST');
        foreach ($arrParams as $key => $val) {
            $objReq->addPostData($key, $val);
        }
        $e = $objReq->sendRequest();
        if (PEAR::isError($e)) {
            throw new OIDConnect_ClientException($e->getMessage());
        }
        $code = $objReq->getResponseCode();
        $body = $objReq->getResponseBody();
        if ($code != 200 && $code != 302) { // XXX endsession で, 正常時に 302 を返してくる
            throw new OIDConnect_ClientException('Illegal HTTP Response is ' . $code. ' ' . $body, $code);
        }

        $objRet = SC_Utils_Ex::jsonDecode($body);
        return $objRet;
    }

    /**
     * GET リクエストを送信し, JSON 形式の結果をデコードします.
     *
     * @param string $url URL
     * @return mixed JSON 形式の結果をデコードしたデータ.
     */
    public function get($url) {
        $objReq = new HTTP_Request();
        $objReq->setUrl($url);
        $e = $objReq->sendRequest();
        if (PEAR::isError($e)) {
            throw new OIDConnect_ClientException($e->getMessage());
        }
        $code = $objReq->getResponseCode();
        $body = $objReq->getResponseBody();
        if ($code != 200 && $code != 302) { // XXX endsession で, 正常時に 302 を返してくる
            throw new OIDConnect_ClientException('Illegal HTTP Response is ' . $code . ' ' . $body, $code);
        }
        $objRet = SC_Utils_Ex::jsonDecode($body);
        return $objRet;
    }
}
