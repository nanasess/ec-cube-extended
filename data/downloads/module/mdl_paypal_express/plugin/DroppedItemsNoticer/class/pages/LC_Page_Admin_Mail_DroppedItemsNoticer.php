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
class LC_Page_Admin_Mail_DroppedItemsNoticer extends LC_Page_Admin_Ex {
    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();
        $this->tpl_mainpage = PLUGIN_UPLOAD_REALDIR . DROPPED_ITEMS_NOTICER_PLUGIN_NAME
                              . '/templates/dropped_items_template_input.tpl';
        $this->tpl_subno    = 'dropped_items_template';
        $this->tpl_mainno   = 'mail';
        $this->tpl_maintitle = 'メール管理';
        $this->tpl_subtitle = 'カゴ落ち通知メルマガテンプレート管理';
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    function action() {
        // 念のために認証をかけておく
        SC_Utils_Ex::sfIsSuccess(new SC_Session_Ex());
        $objFormParam = new SC_FormParam_Ex();
        $this->lfInitParam($objFormParam);

        switch ($this->getMode()) {
            case 'register':
                $objFormParam->setParam($_POST);
                $objFormParam->convParam();
                $this->arrErr = $objFormParam->checkError();
                if (SC_Utils_Ex::isBlank($this->arrErr)) {
                    if ($this->doRegister($objFormParam->getHashArray())) {
                        $this->tpl_onload = 'alert("テンプレートの登録が完了しました");';
                    }
                }
                break;
            default:
        }

        $arrPlugin = SC_Plugin_Util_Ex::getPluginByPluginCode(DROPPED_ITEMS_NOTICER_PLUGIN_NAME);
        $objFormParam->setValue('subject', $arrPlugin['free_field1']);
        $objFormParam->setValue('header', SC_Helper_DroppedItemsNoticer::readTemplate(DROPPED_ITEMS_NOTICER_HEADER));
        $objFormParam->setValue('footer', SC_Helper_DroppedItemsNoticer::readTemplate(DROPPED_ITEMS_NOTICER_FOOTER));
        $this->arrForm = $objFormParam->getFormParamList();
    }

    protected function lfInitParam(SC_FormParam_Ex $objFormParam) {
        $objFormParam->addParam('メールタイトル', 'subject', MTEXT_LEN, 'KVa', array('EXIST_CHECK','SPTAB_CHECK','MAX_LENGTH_CHECK'));
        $objFormParam->addParam('ヘッダー', 'header', LLTEXT_LEN, 'KVa', array('EXIST_CHECK','SPTAB_CHECK','MAX_LENGTH_CHECK'));
        $objFormParam->addParam('フッター', 'footer', LLTEXT_LEN, 'KVa', array('EXIST_CHECK','SPTAB_CHECK','MAX_LENGTH_CHECK'));
    }

    protected function doRegister($arrValues) {
        $result = true;
        if (!SC_Helper_FileManager_Ex::sfWriteFile(DROPPED_ITEMS_NOTICER_HEADER, $arrValues['header'])) {
            $this->arrErr['header'] = 'ヘッダーを書き込めませんでした';
            $result = false;
        }
        if (!SC_Helper_FileManager_Ex::sfWriteFile(DROPPED_ITEMS_NOTICER_FOOTER, $arrValues['footer'])) {
            $this->arrErr['footer'] = 'フッターを書き込めませんでした';
            $result = false;
        }
        if ($result) {
            $objQuery = SC_Query_Ex::getSingletonInstance();
            $objQuery->update('dtb_plugin', array('free_field1' => $arrValues['subject']),
                              'plugin_code = ?', array(DROPPED_ITEMS_NOTICER_PLUGIN_NAME));
        }
        return $result;
    }
}
