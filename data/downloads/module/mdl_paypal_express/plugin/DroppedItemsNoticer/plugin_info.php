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
 * プラグイン の情報クラス.
 *
 * @package DroppedItemsNoticer
 * @author LOCKON CO.,LTD.
 * @version $Id: $
 */
class plugin_info{
    /** プラグインコード(必須)：プラグインを識別する為キーで、他のプラグインと重複しない一意な値である必要がありま. */
    static $PLUGIN_CODE       = "DroppedItemsNoticer";
    /** プラグイン名(必須)：EC-CUBE上で表示されるプラグイン名. */
    static $PLUGIN_NAME       = "PayPal カゴ落ち通知メルマガ配信プラグイン";
    /** プラグインバージョン(必須)：プラグインのバージョン. */
    static $PLUGIN_VERSION    = "1.0";
    /** 対応バージョン(必須)：対応するEC-CUBEバージョン. */
    static $COMPLIANT_VERSION = "2.12.0, 2.12.1, 2.12.2, 2.12.3 2.12.4 2.12.5";
    /** 作者(必須)：プラグイン作者. */
    static $AUTHOR            = "";
    /** 説明(必須)：プラグインの説明. */
    static $DESCRIPTION       = "カゴ落ちをした会員を対象にメルマガ配信が可能です。メルマガを受信した会員は、「PayPal でチェックアウト」ボタンを使用して、スムーズに購入完了できます。このプラグインは、プラグイン管理画面から無効/削除できません。このプラグインを無効にしたい場合は、PayPal Express Checkout 決済モジュールの管理画面から「カゴ落ち通知メルマガ配信機能の使用」を無効にしてください。";
    /** プラグインURL：プラグイン毎に設定出来るURL（説明ページなど） */
    static $PLUGIN_SITE_URL   = "";
    /** プラグイン作者URL：プラグイン毎に設定出来るURL（説明ページなど） */
    static $AUTHOR_SITE_URL   = "";
    /** クラス名(必須)：プラグインのクラス（拡張子は含まない） */
    static $CLASS_NAME       = "DroppedItemsNoticer";
    /** フックポイント：フックポイントとコールバック関数を定義します */
    static $HOOK_POINTS       = array(
        array("loadClassFileChange", 'loadClassFileChange'),
        array("prefilterTransform", 'prefilterTransform'),
        array('LC_Page_Products_List_action_before', 'checkAuthDroppedItemsBeforeInvoke'),
        array('LC_Page_Cart_action_after', 'initOrderTemp'),
        array('LC_Page_Shopping_Complete_action_before', 'trackConversion'),
        array('LC_Page_Admin_Customer_action_before', 'searchDroppedItemsBeforeInvoke'),
        array('LC_Page_Admin_Customer_action_after', 'searchDroppedItemsAfterInvoke'),
        array('LC_Page_Admin_Customer_Edit_action_after', 'resultsDroppedItemsAfterInvoke'),
        array('LC_Page_Admin_Mail_action_before', 'searchDroppedItemsBeforeInvoke'),
        array('LC_Page_Admin_Mail_action_after', 'searchDroppedItemsAfterInvoke'));
    /** ライセンス */
    static $LICENSE        = "LGPL";
}
?>