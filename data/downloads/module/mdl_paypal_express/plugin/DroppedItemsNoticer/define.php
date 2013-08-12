<?php
/** プラグイン名 */
define('DROPPED_ITEMS_NOTICER_PLUGIN_NAME', 'DroppedItemsNoticer');
/** ○日前より本日までを検索 */
define('DROPPED_ITEMS_NOTICER_SEARCH_INTERVAL', 1);
/** ログファイルのパス */
define('DROPPED_ITEMS_NOTICER_LOG', DATA_REALDIR . 'logs/dropped_item_noticer.log');
/** ヘッダテンプレート */
define('DROPPED_ITEMS_NOTICER_HEADER', PLUGIN_UPLOAD_REALDIR . DROPPED_ITEMS_NOTICER_PLUGIN_NAME . '/templates/mail/header.tpl');
/** コンテンツテンプレート */
define('DROPPED_ITEMS_NOTICER_CONTENTS', PLUGIN_UPLOAD_REALDIR . DROPPED_ITEMS_NOTICER_PLUGIN_NAME . '/templates/mail/contents.tpl');
/** フッターテンプレート */
define('DROPPED_ITEMS_NOTICER_FOOTER', PLUGIN_UPLOAD_REALDIR . DROPPED_ITEMS_NOTICER_PLUGIN_NAME . '/templates/mail/footer.tpl');
/** 配信バッチファイル名 */
define('PAYPAL_DROPPED_BATCH_FILENAME', 'paypal_dropped_item_noticer_batch.php');
/** 購入フローから離脱してからカゴ落ちと判定されるまでの時間(秒) **/
define('DROPPED_ITEMS_NOTICER_EXPIRED', MAX_LIFETIME);
/** カゴ落ちから購入完了した場合に, カゴ落ち判定から除外する時間(秒) */
define('DROPPED_ITEMS_IGNORE_INTERVAL', 172800); // 48時間 = 172800秒
define('DROPPED_ITEMS_NOTICER_DEFAULT_SUBJECT', 'お買い物中の商品がございます。');
/** カゴ落ちから購入した場合のBNコード */
define('DROPPED_ITEMS_BUTTONSOURCE', 'EC-CUBE1_cart_EC_JP');