<?php
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
 * SC_Customer_List のカゴ落ち通知メルマガプラグイン用拡張クラス.
 */
class SC_CustomerList_Dropped extends SC_CustomerList_Ex {
    /**
     * コンストラクタ
     */
    function __construct($array, $mode = '') {

        parent::__construct($array, $mode);
        $arrKeys = array('start_year', 'start_month', 'start_day',
                         'end_year', 'end_month', 'end_day');
        foreach ($arrKeys as $key) {
            if (!isset($this->arrSql['search_dropped_' . $key])) {
                $this->arrSql['search_dropped_' . $key] = '';
            }
        }

        // カゴ落ち会員の検索パラメータ設定
        if ((strlen($this->arrSql['search_dropped_start_year']) > 0
             && strlen($this->arrSql['search_dropped_start_month']) > 0
             && strlen($this->arrSql['search_dropped_start_day']) > 0)
            || (strlen($this->arrSql['search_dropped_end_year']) > 0
                && strlen($this->arrSql['search_dropped_end_month']) > 0
                && strlen($this->arrSql['search_dropped_end_day']) > 0)) {
            $arrRegistTime = $this->selectTermRange($this->arrSql['search_dropped_start_year'],
                                                    $this->arrSql['search_dropped_start_month'],
                                                    $this->arrSql['search_dropped_start_day'],
                                                    $this->arrSql['search_dropped_end_year'],
                                                    $this->arrSql['search_dropped_end_month'],
                                                    $this->arrSql['search_dropped_end_day'],
                                                    'A.dropped_date');
            foreach ($arrRegistTime as $data) {
                $this->arrVal[] = $data;
            }
        }

        // カゴ落ちメールは HTMLメールのみ
        if (!SC_Utils_Ex::isBlank($array['search_mail_type'])) {
            $this->setWhere('mailmaga_flg = 1');
        }
    }

    function getList($arrCols = array('dtb_customer.customer_id')) {
        $this->select = SC_Helper_DroppedItemsNoticer::getExtendedSQL($arrCols);
        return $this->getSql(0);
    }

    function getListCount($arrCols = array('dtb_customer.customer_id')) {
        $this->select = SC_Helper_DroppedItemsNoticer::getExtendedSQL($arrCols);
        return $this->getSql(1);
    }

    function setWhere($where) {
        if ($where != '') {
            $where = preg_replace('/customer_id /', 'dtb_customer.customer_id ', $where);
            if ($this->where) {
                $this->where .= ' AND ' . $where;
            } else {
                $this->where = 'WHERE ' . $where;
            }
        }
    }
}
