<?php
/*
 * Copyright(c) 2000-2007 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 */
require_once("../require.php");

class LC_Page {
	var $arrSession;
	var $tpl_mode;
	function LC_Page() {
		$this->tpl_mainpage = 'basis/mail_list.tpl';
		$this->tpl_subnavi = 'basis/subnavi.tpl';
		$this->tpl_mainno = 'basis';
		$this->tpl_subno = 'mail';
		$this->tpl_subtitle = 'メール設定';
	}
}

$conn = new SC_DBConn();
$objQuery = new SC_Query();
$objPage = new LC_Page();
$objView = new SC_AdminView();
$objSess = new SC_Session();

//認証可否の判定
sfIsSuccess($objSess);

//------------------------------------------------------------------

if (count($objPage->arrErr) == 0) {
        
        //-- 検索データ取得
        $sql = "SELECT * FROM dtb_templates";
        $mail_list = $objQuery->getall($sql);
        print_r($mail_list);exit;
        
        // 表示件数設定
        $page_rows = $objPage->arrForm['page_rows'];
        if(is_numeric($page_rows)) {    
            $page_max = $page_rows;
        } else {
            $page_max = SEARCH_PMAX;
        }
        
        if ($objPage->arrForm['search_pageno'] == 0){
            $objPage->arrForm['search_pageno'] = 1;
        }
        
        $offset = $page_max * ($objPage->arrForm['search_pageno'] - 1);
        $objSelect->setLimitOffset($page_max, $offset);     
        
        if ($_POST["mode"] == 'csv') {
            $searchSql = $objSelect->getListCSV($arrColumnCSV);
        }else{
            $searchSql = $objSelect->getList();
        }
        
        $objPage->search_data = $objQuery->conn->getAll($searchSql, $objSelect->arrVal);

        // 行数の取得
            $linemax = $objQuery->conn->getOne( $objSelect->getListCount(), $objSelect->arrVal);
            $objPage->tpl_linemax = $linemax;               // 何件が該当しました。表示用

            // ページ送りの取得
            $objNavi = new SC_PageNavi($_POST['search_pageno'], $linemax, $page_max, "fnCustomerPage", NAVI_PMAX);
            $startno = $objNavi->start_row;
            $objPage->arrPagenavi = $objNavi->arrPagenavi;      
        }


//-----------------------------------------------------------------



$objPage->arrMailTEMPLATE = $arrMAILTEMPLATE;

if ( $_POST['mode'] == 'id_set'){
	// テンプレートプルダウン変更時
	
	if ( sfCheckNumLength( $_POST['template_id']) ){
		$sql = "SELECT * FROM dtb_mailtemplate WHERE template_id = ?";
		$result = $conn->getAll($sql, array($_POST['template_id']) );
		if ( $result ){
			$objPage->arrForm = $result[0];
		} else {
			$objPage->arrForm['template_id'] = $_POST['template_id'];
		}
	}
	
} elseif ( $_POST['mode'] == 'regist' && sfCheckNumLength( $_POST['template_id']) ){

	// POSTデータの引き継ぎ
	$objPage->arrForm = lfConvertParam($_POST);
	$objPage->arrErr = fnErrorCheck($objPage->arrForm);
	
	if ( $objPage->arrErr ){
		// エラーメッセージ
		$objPage->tpl_msg = "エラーが発生しました";
		
	} else {
		// 正常
		lfRegist($conn, $objPage->arrForm);
		
		// 完了メッセージ
		$objPage->tpl_onload = "window.alert('メール設定が完了しました。テンプレートを選択して内容をご確認ください。');";
		unset($objPage->arrForm);
	}
}

$objView->assignobj($objPage);
$objView->display(MAIN_FRAME);

//-----------------------------------------------------------------------------------------------------------------------------------

function lfRegist( $conn, $data ){
	
	$data['creator_id'] = $_SESSION['member_id'];
	
	$sql = "SELECT * FROM dtb_mailtemplate WHERE template_id = ?";
	$result = $conn->getAll($sql, array($_POST['template_id']) );
	if ( $result ){
		$sql_where = "template_id = ". addslashes($_POST['template_id']);
		$conn->query("UPDATE dtb_mailtemplate SET template_id = ?, subject = ?,header = ?, footer = ?,creator_id = ?, update_date = now() WHERE ".$sql_where, $data);
	}else{
		$conn->query("INSERT INTO dtb_mailtemplate (template_id,subject,header,footer,creator_id,update_date,create_date) values ( ?,?,?,?,?,now(),now() )", $data);
	}

}


function lfConvertParam($array) {
	
	$new_array["template_id"] = $array["template_id"];
	$new_array["subject"] = mb_convert_kana($array["subject"] ,"KV");
	$new_array["header"] = mb_convert_kana($array["header"] ,"KV");
	$new_array["footer"] = mb_convert_kana($array["footer"] ,"KV");
	
	return $new_array;
}

/* 入力エラーのチェック */
function fnErrorCheck($array) {
	
	$objErr = new SC_CheckError($array);
	
	$objErr->doFunc(array("テンプレート",'template_id'), array("EXIST_CHECK"));
	$objErr->doFunc(array("メールタイトル",'subject',MTEXT_LEN,"BIG"), array("EXIST_CHECK", "MAX_LENGTH_CHECK"));
	$objErr->doFunc(array("ヘッダー",'header',LTEXT_LEN,"BIG"), array("MAX_LENGTH_CHECK"));
	$objErr->doFunc(array("フッター",'footer',LTEXT_LEN,"BIG"), array("MAX_LENGTH_CHECK"));

	return $objErr->arrErr;
}

?>