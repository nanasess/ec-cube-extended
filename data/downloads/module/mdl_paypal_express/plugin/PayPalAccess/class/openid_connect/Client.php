<?php

/**
 * OpenID Connect のクライアントインターフェイス.
 */
interface OIDConnect_Client {

    /**
     * Authorization endpoint から URL を取得します.
     */
    function getAuthUrl();

    /**
     * Access Token を取得します.
     */
    function getAccessToken($code);

    /**
     * Access Token を更新します.
     */
    function refreshAccessToken($refresh_token);

    /**
     * Access Token を検証します.
     */
    function validateToken($id_token);

    /**
     * Profile を取得します.
     */
    function getProfile($access_token);

    /**
     * ログアウトします.
     */
    function endSession($id_token);
}
