<?php
require_once(realpath(dirname( __FILE__)) . '/../../define.php');
/*
 * DroppedItemsNoticer
 * Copyright (C) 2012 LOCKON CO.,LTD. All Rights Reserved.
 * http://www.lockon.co.jp/
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 *
 */
class SC_Helper_DroppedItemsNoticer {

    /**
     * 会員一覧検索をする処理（ページング処理付き、管理画面用共通処理）
     *
     * @param array $arrParam 検索パラメーター連想配列
     * @param string $limitMode ページングを利用するか判定用フラグ
     * @return array( integer 全体件数, mixed 会員データ一覧配列, mixed SC_PageNaviオブジェクト)
     */
    public static function sfGetSearchData($arrParam, $limitMode = '') {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objSelect = new SC_CustomerList_Dropped($arrParam, 'customer');
        $page_max = SC_Utils_Ex::sfGetSearchPageMax($arrParam['search_page_max']);
        $disp_pageno = $arrParam['search_pageno'];
        if ($disp_pageno == 0) {
            $disp_pageno = 1;
        }
        $offset = intval($page_max) * (intval($disp_pageno) - 1);
        if ($limitMode == '') {
            $objQuery->setLimitOffset($page_max, $offset);
        }
        $arrCols = array('dtb_customer.customer_id',
                         'name01',
                         'name02',
                         'kana01',
                         'kana02',
                         'sex',
                         'email',
                         'email_mobile',
                         'tel01',
                         'tel02',
                         'tel03',
                         'pref',
                         'status',
                         'update_date',
                         'mailmaga_flg');
        $arrData = $objQuery->getAll($objSelect->getList($arrCols), $objSelect->arrVal);

        // 該当全体件数の取得
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $linemax = $objQuery->getOne('SELECT COUNT(*) AS count FROM (' . $objSelect->getListCount() . ' GROUP BY dtb_customer.customer_id) C', $objSelect->arrVal);

        // ページ送りの取得
        $objNavi = new SC_PageNavi_Ex($arrParam['search_pageno'],
                                    $linemax,
                                    $page_max,
                                    'fnNaviSearchOnlyPage',
                                    NAVI_PMAX);
        return array($linemax, $arrData, $objNavi);
    }

    public static function addSearchParam(SC_FormParam $objFormParam) {
        $objFormParam->addParam('編集対象会員ID', 'edit_customer_id', INT_LEN, 'n', array('NUM_CHECK','MAX_LENGTH_CHECK'));
        // search_htmlmail が検索対象になっている場合のみパラメータを追加する
        if (isset($_POST['search_htmlmail'])) {
            $objFormParam->addParam('配信形式', 'search_htmlmail', INT_LEN, 'n', array('NUM_CHECK','MAX_LENGTH_CHECK'));
        }
        $objFormParam->addParam('配信メールアドレス種別', 'search_mail_type', INT_LEN, 'n', array('NUM_CHECK','MAX_LENGTH_CHECK'));
        $objFormParam->addParam('カゴ落ち会員の検索(開始年)', 'search_dropped_start_year', INT_LEN, 'n', array('NUM_CHECK','MAX_LENGTH_CHECK'));
        $objFormParam->addParam('カゴ落ち会員の検索(開始月)', 'search_dropped_start_month', INT_LEN, 'n', array('NUM_CHECK','MAX_LENGTH_CHECK'));
        $objFormParam->addParam('カゴ落ち会員の検索(開始日)', 'search_dropped_start_day', INT_LEN, 'n', array('NUM_CHECK','MAX_LENGTH_CHECK'));
        $objFormParam->addParam('カゴ落ち会員の検索(終了年)', 'search_dropped_end_year', INT_LEN, 'n', array('NUM_CHECK','MAX_LENGTH_CHECK'));
        $objFormParam->addParam('カゴ落ち会員の検索(終了月)', 'search_dropped_end_month', INT_LEN, 'n', array('NUM_CHECK','MAX_LENGTH_CHECK'));
        $objFormParam->addParam('カゴ落ち会員の検索(終了日)', 'search_dropped_end_day', INT_LEN, 'n', array('NUM_CHECK','MAX_LENGTH_CHECK'));
    }

    public static function sfCheckErrorSearchParam(SC_FormParam $objFormParam) {
        $arrErr = SC_Helper_Customer_Ex::sfCheckErrorSearchParam($objFormParam);
        if (SC_Utils_Ex::isBlank($arrErr)) {
            $objErr = new SC_CheckError_Ex($objFormParam->getHashArray());
            $objErr->doFunc(array('カゴ落ち会員の検索(開始)', 'search_dropped_start_year', 'search_dropped_start_month', 'search_dropped_start_day',), array('CHECK_DATE'));
            $objErr->doFunc(array('カゴ落ち会員の検索(終了)', 'search_dropped_end_year', 'search_dropped_end_month', 'search_dropped_end_day'), array('CHECK_DATE'));
            $objErr->doFunc(array('カゴ落ち会員の検索(開始)','カゴ落ち会員の検索(終了)', 'search_dropped_start_year', 'search_dropped_start_month', 'search_dropped_start_day', 'search_dropped_end_year', 'search_dropped_end_month', 'search_dropped_end_day'), array('CHECK_SET_TERM'));
            if (!SC_Utils_Ex::isBlank($objErr->arrErr)) {
                $arrErr = array_merge($arrErr, $objErr->arrErr);
            }
        }
        return $arrErr;
    }

    /**
     * カゴ落ち商品を検索します.
     *
     * @param integer $customer_id 会員ID
     * @return array カゴ落ち商品履歴の配列
     */
    public static function findDroppedItemsHistories($customer_id, $add_where = '', $arrParams = array()) {
        $col = 'T1.customer_id, T1.order_id, T1.update_date AS dropped_date, T1.session';
        $from = self::getExtendedSQLFrom();
        $where = self::getExtendedSQLWhere();
        $where .= ' AND T1.customer_id = ?';
        if (!SC_Utils_Ex::isBlank($add_where)) {
            $where .= ' AND ' . $add_where;
        }
        array_unshift($arrParams, $customer_id);
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->setOrder('T1.create_date DESC');
        $arrHistory = $objQuery->select($col, $from, $where, $arrParams);
        $arrDroppedHistories = array();
        $i = 0;
        foreach ($arrHistory as $key => $arrTmp) {
            $arrSession = unserialize($arrTmp['session']);
            foreach ($arrSession['cart'] as $cart_key => $arrCart) {
                foreach ($arrCart as $cart_no => $arrItem) {
                    if (is_array($arrItem['productsClass'])) {
                        $arrDroppedHistories[$i] = $arrItem;
                        $arrDroppedHistories[$i]['dropped_date'] = $arrTmp['dropped_date'];
                        $i++;
                    }
                }
            }
        }
        return $arrDroppedHistories;
    }

    /**
     * テンプレートを読み込む.
     */
    public static function readTemplate($path) {
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return '';
    }

    /**
     * 受注一時情報の最終更新日時から, セッションの有効期限を過ぎた検索条件を返す.
     *
     * RDBMS により書式が異なるため, DB_TYPE により分岐
     */
    public static function getSessionIntervalQuery() {
        if (DB_TYPE == 'mysql') { // TODO 抽象化したい
            return 'unix_timestamp(T1.update_date) + '. DROPPED_ITEMS_NOTICER_EXPIRED . ' < unix_timestamp(now())' ;
        } else {
            return 'extract(epoch from T1.update_date) + ' . DROPPED_ITEMS_NOTICER_EXPIRED . ' < extract(epoch from CURRENT_TIMESTAMP)';
        }
    }

    /**
     * カゴ落ちから購入完了した場合, カゴ落ちの検索条件より除外する期間の検索条件を返す.
     *
     * RDBMS により書式が異なるため, DB_TYPE により分岐
     */
    public static function getIgnoreIntervalQuery() {
        if (DB_TYPE == 'mysql') { // TODO 抽象化したい
            return 'unix_timestamp(TT3.complete_date) + ' . DROPPED_ITEMS_IGNORE_INTERVAL . ' > unix_timestamp(T1.create_date) ';
        } else {
            return 'extract(epoch from TT3.complete_date) + ' . DROPPED_ITEMS_IGNORE_INTERVAL . ' > extract(epoch from T1.create_date) ';
        }
    }

    /**
     * カゴ落ち会員検索用に拡張したSQL(FROM句)を返す.
     */
    public static function getExtendedSQLFrom() {
        $from = <<< __EOF__
           dtb_order_temp T1
   LEFT JOIN dtb_order T2 ON T1.order_temp_id = T2.order_temp_id
   LEFT JOIN plg_droppeditemsnoticer_order T3 ON T2.order_id = T3.order_id
__EOF__;
        return $from;
    }

    /**
     * カゴ落ち会員検索用に拡張したSQL(WHERE句)を返す.
     */
    public static function getExtendedSQLWhere() {
        $session_interval = self::getSessionIntervalQuery();
        $ignore_interval = self::getIgnoreIntervalQuery();
        $where = <<< __EOF__
        (T2.order_id IS NULL OR T3.order_id IS NULL)
     AND {$session_interval}
     AND NOT EXISTS
       (SELECT customer_id
        FROM dtb_order TT
   LEFT JOIN plg_droppeditemsnoticer_order TT3 ON TT.order_id = TT3.order_id
        WHERE (TT.customer_id = T1.customer_id
               OR TT.order_email = T1.order_email)
          AND TT3.order_id IS NOT NULL
          AND TT.del_flg = 0
          AND TT.order_temp_id <> T1.order_temp_id
          AND {$ignore_interval})
__EOF__;
        return $where;
    }

    /**
     * カゴ落ち会員検索用に拡張したSQLを返す.
     */
    public static function getExtendedSQL($arrCols) {
        $cols = implode(',', $arrCols);
        $from = self::getExtendedSQLFrom();
        $where = self::getExtendedSQLWhere();
        $sql = <<< __EOF__
SELECT DISTINCT {$cols}
FROM dtb_customer
JOIN
  ( SELECT T1.customer_id,
           T1.update_date AS dropped_date,
           T1.session
    FROM {$from}
    WHERE {$where}
    ) A ON dtb_customer.customer_id = A.customer_id
__EOF__;
        return $sql;
    }

    /**
     * 認証コードを生成する.
     */
    public static function createAuthcode($customer_id, $product_class_id) {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrCustomer = $objQuery->getRow('customer_id, salt',
                                         'dtb_customer', 'customer_id = ?',
                                         array($customer_id));
        $authcode = SC_Utils_Ex::sfGetHashString($customer_id . ':' . $product_class_id . ':' . SC_Utils_Ex::sfGetRandomString(10),
                                                 $arrCustomer['salt']);
        // 重複していたら再帰実行
        if ($objQuery->exists('plg_droppeditemsnoticer_auth', 'authcode = ?',
                              array($authcode))) {
            $authcode = self::createAuthcode($customer_id, $product_class_id);
        }
        $objQuery->insert('plg_droppeditemsnoticer_auth', array('authcode' => $authcode,
                                                                'customer_id' => $customer_id,
                                                                'product_class_id' => $product_class_id));
        return $authcode;
    }

    /**
     * カゴ落ち通知メルマガ配信を使用するか？
     */
    public static function useNoticer() {
        $arrPlugin = SC_Plugin_Util::getPluginByPluginCode(DROPPED_ITEMS_NOTICER_PLUGIN_NAME);
        if ($arrPlugin['enable'] == PLUGIN_ENABLE_TRUE) {
            return true;
        }
        return false;
    }
}