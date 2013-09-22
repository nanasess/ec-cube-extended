<?php
/*
 * PayPalAccess
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
 * プラグイン のアップデート用クラス.
 *
 * @author LOCKON CO.,LTD.
 * @version $Id: $
 */
class plugin_update{

    function update($arrPlugin) {
        // uninstall
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $file = realpath(dirname( __FILE__)) . '/sql/drop_table.sql';
        SC_Helper_PayPalAccess::executeSQL($file);

        copy(realpath(dirname( __FILE__)) . "/logo.png", PLUGIN_HTML_REALDIR . $arrPlugin['plugin_code'] . "/logo.png");
        copy(realpath(dirname( __FILE__)) . "/copy/index.php", PLUGIN_HTML_REALDIR . $arrPlugin['plugin_code'] . "/index.php");
        copy(realpath(dirname( __FILE__)) . "/copy/jquery.paypalaccess_util.js", PLUGIN_HTML_REALDIR . $arrPlugin['plugin_code'] . "/jquery.paypalaccess_util.js");
        copy(realpath(dirname( __FILE__)) . "/copy/plg_paypalaccess.php", HTML_REALDIR . "frontparts/bloc/plg_paypalaccess.php");
        copy(realpath(dirname( __FILE__)) . "/templates/paypalaccess.tpl", TEMPLATE_REALDIR . 'frontparts/bloc/paypalaccess.tpl');
        copy(realpath(dirname( __FILE__)) . "/templates/paypalaccess.tpl", SMARTPHONE_TEMPLATE_REALDIR . 'frontparts/bloc/paypalaccess.tpl');

        $arrTables = $objQuery->listTables();
        if (!in_array(array('plg_paypalaccess_claims', 'plg_paypalaccess_token'), $arrTables)) {
            $file = realpath(dirname( __FILE__)) . '/sql/create_table_' . DB_TYPE . '.sql';
            SC_Helper_PayPalAccess::executeSQL($file);
        }

        self::registerBloc($arrPlugin, DEVICE_TYPE_PC);
        self::registerBloc($arrPlugin, DEVICE_TYPE_SMARTPHONE);
    }

    /**
     * ブロックを登録する.
     */
    function registerBloc($arrPlugin, $device_type_id) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $arrValues['device_type_id'] = $device_type_id;
        $arrValues['bloc_id'] = 1 + $objQuery->max('bloc_id', 'dtb_bloc', 'device_type_id = ?',
                                                   array($arrValues['device_type_id']));
        $arrValues['bloc_name'] = 'PayPal アカウントでログインボタン';
        $arrValues['tpl_path'] = 'paypalaccess.tpl';
        $arrValues['filename'] = 'paypalaccess';
        $arrValues['php_path'] = 'frontparts/bloc/plg_paypalaccess.php';
        $arrValues['create_date'] = 'CURRENT_TIMESTAMP';
        $arrValues['update_date'] = 'CURRENT_TIMESTAMP';
        $arrValues['deletable_flg'] = '1';
        $arrValues['plugin_id'] = $arrPlugin['plugin_id'];
        $where = 'plugin_id = ? AND device_type_id = ?';
        $arrWhere = array($arrPlugin['plugin_id'], $device_type_id);
        if ($objQuery->exists('dtb_bloc', $where, $arrWhere)) {
            $objQuery->update('dtb_bloc', $arrValues, $where, $arrWhere);
        } else {
            $objQuery->insert('dtb_bloc', $arrValues);
        }
    }
}
?>