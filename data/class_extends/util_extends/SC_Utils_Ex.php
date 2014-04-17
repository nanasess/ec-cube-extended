<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2013 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

require_once CLASS_REALDIR . 'util/SC_Utils.php';

/**
 * 各種ユーティリティクラス(拡張).
 *
 * SC_Utils をカスタマイズする場合はこのクラスを使用する.
 *
 * @package Util
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class SC_Utils_Ex extends SC_Utils
{
    function sfInitInstall() {
        // インストール済みが定義されていない。
        if (!defined('ECCUBE_INSTALL')) {
            $phpself = $_SERVER['SCRIPT_NAME'];
            if (strpos('/install/', $phpself) === false) {
                $path = substr($phpself, 0, strpos($phpself, basename($phpself)));
                $install_url = SC_Utils_Ex::searchInstallerPath($path);
                header('Location: ' . $install_url);
                exit;
            }
        }
        $path = HTML_REALDIR . 'install/' . DIR_INDEX_FILE;
        if (file_exists($path)) {
            SC_Utils_Ex::sfErrorHeader('&gt;&gt; /install/' . DIR_INDEX_FILE . ' は、インストール完了後にファイルを削除してください。削除するには<a href="' . ROOT_URLPATH . 'deleteInstaller.php">こちら</a>をクリックしてください。');
        }
    }

}
