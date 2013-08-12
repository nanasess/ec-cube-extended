<?php
/**
 * PayPal Access で利用可能な拡張 scope
 */
interface PayPalAccessScope extends OIDConnect_Scope {
    const EXTENDED_SCOPE = 'https://uri.paypal.com/services/paypalattributes';
}