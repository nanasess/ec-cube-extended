<?php
// モジュール名
define('MDL_PAYPAL_EXPRESS_CODE', 'mdl_paypal_express');
// 希望数量の入力は許可しない
define('PAYPAL_EXPRESS_UNDEFINED_QUANTITY', '0');
// 日本円のみ対応
define('PAYPAL_EXPRESS_CURRENCY_CODE', 'JPY');
// 国コード: 日本
define('PAYPAL_EXPRESS_COUNTRY_CODE', 'JP');
// 文字エンコーディングは UTF-8
define('PAYPAL_EXPRESS_CHARSET', 'utf-8');
// 配送先住所の入力を求めない
define('PAYPAL_EXPRESS_NO_SHIPPING', '1');
// 通信欄の入力を求めない
define('PAYPAL_EXPRESS_NO_NOTE', '1');
// PayPal ログイン画面の URL
define('PAYPAL_EXPRESS_LINK_URL', "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout");
define('PAYPAL_EXPRESS_LINK_URL_SANDBOX', "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout");
// NVP API のエンドポイント
define('PAYPAL_EXPRESS_NVP_URL', 'https://api-3t.paypal.com/nvp');
define('PAYPAL_EXPRESS_NVP_URL_SANDBOX', 'https://api-3t.sandbox.paypal.com/nvp');
// エクスプレスチェックアウトボタン
define('PAYPAL_EXPRESS_CHECKOUT_BUTTON', 'https://www.paypal.com/ja_JP/JP/i/btn/btn_xpressCheckout.gif');
// NVP API のバージョン
define('PAYPAL_EXPRESS_API_VERSION', '71.0');
// 支払の動作
define('PAYPAL_EXPRESS_PAYMENTACTION', 'Sale');
// ログファイルのパス
define("PAYPAL_EXPRESS_LOG_PATH", DATA_REALDIR. "logs/paypal.log");
// BUTTONSOURCE
define('PAYPAL_EXPRESS_BUTTONSOURCE', 'EC-CUBE_cart_EC_JP');
/** API署名を取得するURL */
define('PAYPAL_API_SIGNATURE_URL', 'https://www.paypal.com/jp/ja/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true');
define('PAYPAL_SANDBOX_API_SIGNATURE_URL' , 'https://www.sandbox.paypal.com/jp/ja/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true');
/** PayPal について URL */
define('PAYPAL_OLC_URL', 'https://www.paypal.com/jp/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside');
/** PayPal が使えますバナー */
define('PAYPAL_NOW_ACCEPTING_URL', 'https://www.paypal.com/ja_JP/JP/i/bnr/bnr_nowAccepting_150x60.gif');
/** PayPal が使えますバナー配置 */
define('PAYPAL_USE_BANNER_NONE', 0);
define('PAYPAL_USE_BANNER_RIGHT', 1);
define('PAYPAL_USE_BANNER_LEFT', 2);
