<?php

/**
 * OpenID Connect クライアントの例外
 */
class OIDConnect_ClientException extends Exception {

    /** HTTP Status 400 を返した場合のエラーコード */
    const BAD_REQUEST_ERROR_CODE = 400;
    /** HTTP Status 401 を返した場合のエラーコード */
    const AUTH_ERROR_CODE = 401;

}