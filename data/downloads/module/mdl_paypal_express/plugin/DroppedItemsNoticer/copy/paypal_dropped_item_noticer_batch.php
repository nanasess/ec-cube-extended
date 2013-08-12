<?php
/*
 * カゴ落ち通知メルマガ配信プログラム
 *
 * Webから実行された場合は, 画面の検索条件を引き継ぎます.
 * cli で実行された場合は, 前日カゴ落ちした会員に対して自動的に配信します.
 */
require_once(realpath(dirname( __FILE__)) . '/../require.php');
require_once(PLUGIN_UPLOAD_REALDIR . 'DroppedItemsNoticer/define.php');
require_once(PLUGIN_UPLOAD_REALDIR . 'DroppedItemsNoticer/class/SC_CustomerList_Dropped.php');
require_once(PLUGIN_UPLOAD_REALDIR . 'DroppedItemsNoticer/class/helper/SC_Helper_DroppedItemsNoticer.php');

set_time_limit(0);
if (!defined('WITH_PAYPAL_ADMIN')) {
    // Webから実行されないように
    if ($_SERVER["REQUEST_METHOD"] == "POST" || $_SERVER["REQUEST_METHOD"] == "GET") {
        printOut($_SERVER["REQUEST_METHOD"] . " Requests by" . print_r($_REQUEST, true) . ", this script is command line only.");
        header("HTTP/1.1 400 Bad Request");
        exit(1);
    }
    while (@ob_end_flush());
}

printOut('"************************** PayPal Dropped Items Noticer Batch START *************************"');
echo $log;

if (!SC_Helper_DroppedItemsNoticer::useNoticer()) {
    printOut('PayPal Dropped Items Noticer OFF. exit.');
    exit(1);
}

$arrPlugin = SC_Plugin_Util_Ex::getPluginByPluginCode(DROPPED_ITEMS_NOTICER_PLUGIN_NAME);

// cli で実行された場合の検索期間を設定
if (SC_Utils_Ex::isBlank($arrParams)) {
    $yesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
    $arrParams = array('search_dropped_start_year'  => date('Y', $yesterday),
                       'search_dropped_start_month' => date('m', $yesterday),
                       'search_dropped_start_day'   => date('d', $yesterday),
                       'search_dropped_end_year'    => date('Y', $yesterday),
                       'search_dropped_end_month'   => date('m', $yesterday),
                       'search_dropped_end_day'     => date('d', $yesterday));

}

printOut('Target params ' . print_r($arrParams, true));
// 対象会員を検索
$objSelect = new SC_CustomerList_Dropped($arrParams, 'customer');
$arrCols = array('dtb_customer.customer_id',
                 'name01',
                 'name02',
                 'email',
                 'status',
                 'mailmaga_flg');
$objQuery = SC_Query_Ex::getSingletonInstance();
$arrCustomers = $objQuery->getAll($objSelect->getList($arrCols), $objSelect->arrVal);

// 検索期間の WHERE を生成
$objInSelect = new SC_SelectSql();
$arrInParams = $objInSelect->selectTermRange($arrParams['search_dropped_start_year'],
                                             $arrParams['search_dropped_start_month'],
                                             $arrParams['search_dropped_start_day'],
                                             $arrParams['search_dropped_end_year'],
                                             $arrParams['search_dropped_end_month'],
                                             $arrParams['search_dropped_end_day'],
                                             'T1.update_date');

// 顧客ごとにカゴ落ち商品を検索
$count = 0;
foreach ($arrCustomers as $arrCustomer) {
    $arrCustomerCart = SC_Helper_DroppedItemsNoticer::findDroppedItemsHistories($arrCustomer['customer_id'],
                                                                                str_replace('WHERE ', '', $objInSelect->where),
                                                                                $arrInParams);
    printOut('dropped customer_id: ' . $arrCustomer['customer_id']);
    // カートの内容を product_class_id でマージ
    $arrDropped = array();
    foreach ($arrCustomerCart as $arrCart) {

        // 商品チェック
        $objProduct = new SC_Product_Ex();
        $arrProduct = $objProduct->getProductsClass($arrCart['id']);

        // 存在チェック
        if (SC_Utils_Ex::isBlank($arrProduct)) {
            printOut('customer_id: ' . $arrCustomer['customer_id'] . ' product_class_id: ' . $arrCart['id'] . ' products is not found.');
            continue;
        }
        // 表示チェック
        $arrProductDetail = $objProduct->getDetail($arrProduct['product_id']);
        if ($arrProductDetail['status'] == 2) {
            printOut('customer_id: ' . $arrCustomer['customer_id'] . ' product_id: ' . $arrProduct['product_id'] . ' products is hidden.');
            continue;
        }

        // 在庫チェック
        $limit = $objProduct->getBuyLimit($arrProduct);
        if (!is_null($limit) && $limit < 1) {
            printOut('customer_id: ' . $arrCustomer['customer_id'] . ' product_class_id: ' . $arrCart['id'] . ' products is soldout.');
            continue;
        }

        $arrDetail = $objProduct->getDetail($arrProduct['product_id']);
        $arrCart['main_list_comment'] = $arrDetail['main_list_comment'];
        $arrCart['price'] = SC_Helper_DB::sfCalcIncTax($arrCart['price']);
        // 認証キーを格納
        $arrCart['authcode'] = SC_Helper_DroppedItemsNoticer::createAuthcode($arrCustomer['customer_id'], $arrCart['id']);
        $arrDropped[$arrCart['id']] = $arrCart;
    }

    printOut('customer_id: ' . $arrCustomer['customer_id'] . ' arrCart => ' . print_r($arrDropped, true));

    if (SC_Utils_Ex::isBlank($arrDropped)) {
        printOut('customer_id: ' . $arrCustomer['customer_id'] . ' arrCart is empty.');
        continue;
    }
    // メールのデータを生成
    $arrSiteInfo = SC_Helper_DB_Ex::sfGetBasisData();
    $objMailView = new SC_SiteView_Ex();
    $customer_name = $arrCustomer['name01'] . ' ' . $arrCustomer['name02'];

    $objMailView->assignarray(array('arrCart' => $arrDropped,
                                    'arrSiteInfo' => $arrSiteInfo,
                                    'subject' => $arrPlugin['free_field1']));
    $body = $objMailView->fetch(DROPPED_ITEMS_NOTICER_CONTENTS);
    $body = preg_replace('/{name}/', $customer_name, $body);
    $subject = preg_replace('/{name}/', $customer_name, $arrPlugin['free_field1']);

    $objMail = new SC_SendMail_Ex();
    $objMail->setItem(
        $arrCustomer['email'],        // 宛先
        $subject,
        $body,
        $arrSiteInfo['email03'],      // 送信元メールアドレス
        $arrSiteInfo['shop_name'],    // 送信元名
        $arrSiteInfo['email03'],      // reply_to
        $arrSiteInfo['email04'],      // return_path
        $arrSiteInfo['email04']       // errors_to
                      );
    $objMail->sendHtmlMail();
    $count++;
}

// すべて終了したらメールを送信
$body =  <<< __EOF__
カゴ落ち通知メルマガ配信が完了しました。

合計: {$count} 件

__EOF__;
$CONF = SC_Helper_DB_Ex::sfGetBasisData();
$objMail = new SC_SendMail();
$objMail->setItem(
            ''                         // 宛先
            , 'カゴ落ち通知メルマガ配信完了' // サブジェクト
            , $body                    // 本文
            , $CONF["email03"]         // 配送元アドレス
            , $CONF["shop_name"]       // 配送元 名前
            , $CONF["email03"]         // reply_to
            , $CONF["email04"]         // return_path
            , $CONF["email04"]         // Errors_to
        );
// 宛先の設定
$objMail->setTo($CONF["email01"]);
$objMail->sendMail();


$log = '"************************** PayPal Dropped Items Noticer Batch Finish *************************"' . PHP_EOL;
$log .= 'Total: ' . $count  . PHP_EOL;
printOut($log);


/**
 * 文字列を出力する.
 *
 * @param mixed $msg 出力するメッセージ.
 * @param string $filename 出力元のファイル名
 * @param string $line 出力元の行
 * @param resources $stream 出力を行なうストリーム. 省略した場合は標準出力
 * @return void
 */
function printOut($msg, $filename = '', $line = '', $stream = STDOUT) {
    // 日付の取得
    $arrMicrotime = explode('.', microtime(true));
    $microtime = strlen($arrMicrotime['1']) > 0 ? '.' . $arrMicrotime['1'] : '';
    $today = date("Y/m/d H:i:s") . $microtime;
    $mess = $today . ' ' . $filename . ':' . $line . ' ' . print_r($msg, true) . PHP_EOL;
    GC_Utils_Ex::gfPrintLog($mess, DROPPED_ITEMS_NOTICER_LOG);
    fwrite($stream, $mess);
}
