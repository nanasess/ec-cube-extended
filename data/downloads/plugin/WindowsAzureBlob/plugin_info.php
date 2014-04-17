<?php
/**
 * プラグイン の情報クラス.
 *
 * @package WindowsAzureBlob
 * @author Kentaro Ohkouchi
 * @version $Id: $
 */
class plugin_info{
    /** プラグインコード(必須)：プラグインを識別する為キーで、他のプラグインと重複しない一意な値である必要があります. */
    static $PLUGIN_CODE       = "WindowsAzureBlob";
    /** プラグイン名(必須)：EC-CUBE上で表示されるプラグイン名. */
    static $PLUGIN_NAME       = "Microsoft Windows Azure Blob プラグイン";
    /** プラグインバージョン(必須)：プラグインのバージョン. */
    static $PLUGIN_VERSION    = "1.0";
    /** 対応バージョン(必須)：対応するEC-CUBEバージョン. */
    static $COMPLIANT_VERSION = "2.12.0";
    /** 作者(必須)：プラグイン作者. */
    static $AUTHOR            = "Kentaro Ohkouchi (Loop AZ)";
    /** 説明(必須)：プラグインの説明. */
    static $DESCRIPTION       = "Microsoft Windows Azure Blob 対応プラグインです。 特殊なドライバを必要とせず、 REST API を使用しますので、 Linux 等 Windows 以外の OS でも使用できます。";
    /** プラグインURL：プラグイン毎に設定出来るURL（説明ページなど） */
    static $PLUGIN_SITE_URL   = "";
    /** プラグイン作者URL：プラグイン毎に設定出来るURL（説明ページなど） */
    static $AUTHOR_SITE_URL   = "http://www.loop-az.co.jp/";
    /** クラス名(必須)：プラグインのクラス（拡張子は含まない） */
    static $CLASS_NAME        = "WindowsAzureBlob";
    /** フックポイント：フックポイントとコールバック関数を定義します */
    static $HOOK_POINTS       = array(
        array("loadClassFileChange", 'loadClassFileChange'));
    /** プラグインのライセンス. */
    static $LICENSE           = "Apache_License_2.0";
}
?>