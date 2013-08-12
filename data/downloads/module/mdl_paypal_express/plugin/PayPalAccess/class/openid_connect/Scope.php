<?php

/**
 * OpenID Connect の Scope を表す列挙型
 */
interface OIDConnect_Scope {
    const OPENID = 'openid';
    const PROFILE = 'profile';
    const EMAIL = 'email';
    const ADDRESS = 'address';
    const PHONE = 'phone';
}