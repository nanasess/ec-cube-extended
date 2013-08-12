<!--{*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2011 LOCKON CO.,LTD. All Rights Reserved.
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
 *}-->
<script type="text/javascript" src="<!--{$smarty.const.ROOT_URLPATH}-->js/jquery.facebox/facebox.js"></script>
<link rel="stylesheet" type="text/css" href="<!--{$smarty.const.ROOT_URLPATH}-->js/jquery.facebox/facebox.css" media="screen" />
<script type="text/javascript">//<![CDATA[
var send = true;

function fnCheckSubmit() {
    if(send) {
        send = false;
        return true;
    } else {
        alert("只今、処理中です。しばらくお待ち下さい。");
        return false;
    }
}

$(document).ready(function() {
    $('a.expansion').facebox({
        loadingImage : '<!--{$smarty.const.ROOT_URLPATH}-->js/jquery.facebox/loading.gif',
        closeImage   : '<!--{$smarty.const.ROOT_URLPATH}-->js/jquery.facebox/closelabel.png'
    });
});
//]]></script>

<!--CONTENTS-->
<div id="undercolumn">
    <div id="undercolumn_shopping">
        <h2 class="title"><!--{$tpl_title|h}--></h2>

        <!--{if $tpl_message}-->
        <p class="attention">エラーメッセージ:<br /><!--{$tpl_message}--></p>
        <!--{else}-->
        <p class="information">PayPal決済画面で指定いただいた住所に送付いたします。<br />
        送料が追加・変更されたため、お支払いいただく金額が変更されている可能性がありますので、ご確認ください。<br />
            よろしければ、「ご注文完了ページへ」ボタンをクリックしてください。</p>
        <!--{/if}-->
        <form name="form1" id="form1" method="post" action="?">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
        <input type="hidden" name="mode" value="confirm" />
        <input type="hidden" name="uniqid" value="<!--{$tpl_uniqid}-->" />

        <div class="btn_area">
           <p><input type="submit" value="ご注文完了ページへ" class="spbtn spbtn-shopping btn" width="130" height="30" alt="ご注文完了ページへ" name="next" id="next" onclick="return fnCheckSubmit();" /></p>
           <p><a href="javascript:;" onclick="fnFormModeSubmit('form1', 'return', '', ''); return false;" class="spbtn spbtn-medeum">戻る</a></p>
        </div>

        <table summary="ご注文内容確認">
            <colgroup width="10%"></colgroup>
            <colgroup width="40%"></colgroup>
            <colgroup width="20%"></colgroup>
            <colgroup width="10%"></colgroup>
            <colgroup width="20%"></colgroup>
            <tr>
                <th scope="col">商品写真</th>
                <th scope="col">商品名</th>
                <th scope="col">単価</th>
                <th scope="col">数量</th>
                <th scope="col">小計</th>
            </tr>
            <!--{foreach from=$arrOrderDetails item=item}-->
                <tr>
                    <td class="alignC">
                        <a
                            <!--{if $item.productsClass.main_image|strlen >= 1}--> href="<!--{$smarty.const.IMAGE_SAVE_URLPATH}--><!--{$item.productsClass.main_image|sfNoImageMainList|h}-->" class="expansion" target="_blank"
                            <!--{/if}-->
                        >
                            <img src="<!--{$smarty.const.ROOT_URLPATH}-->resize_image.php?image=<!--{$item.productsClass.main_list_image|sfNoImageMainList|h}-->&amp;width=65&amp;height=65" alt="<!--{$item.productsClass.name|h}-->" /></a>
                    </td>
                    <td>
                        <ul>
                            <li><strong><!--{$item.productsClass.name|h}--></strong></li>
                            <!--{if $item.productsClass.classcategory_name1 != ""}-->
                            <li><!--{$item.productsClass.class_name1}-->：<!--{$item.productsClass.classcategory_name1}--></li>
                            <!--{/if}-->
                            <!--{if $item.productsClass.classcategory_name2 != ""}-->
                            <li><!--{$item.productsClass.class_name2}-->：<!--{$item.productsClass.classcategory_name2}--></li>
                            <!--{/if}-->
                        </ul>
                    </td>
                    <td class="alignR">
                        <!--{$item.productsClass.price02|sfCalcIncTax:$arrInfo.tax:$arrInfo.tax_rule|number_format}-->円
                    </td>
                    <td class="alignR"><!--{$item.quantity|number_format}--></td>
                    <td class="alignR"><!--{$item.price|sfCalcIncTax:$arrInfo.tax:$arrInfo.tax_rule|number_format}-->円</td>
                </tr>
            <!--{/foreach}-->
            <tr>
                <th colspan="4" class="alignR" scope="row">小計</th>
                <td class="alignR"><!--{$arrOrder.subtotal|number_format}-->円</td>
            </tr>
            <!--{if $smarty.const.USE_POINT !== false}-->
                <tr>
                    <th colspan="4" class="alignR" scope="row">値引き（ポイントご使用時）</th>
                    <td class="alignR">
                        <!--{assign var=discount value=`$arrOrder.use_point*$smarty.const.POINT_VALUE`}-->
                        -<!--{$discount|number_format|default:0}-->円</td>
                </tr>
            <!--{/if}-->
            <tr>
                <th colspan="4" class="alignR" scope="row">送料</th>
                <td class="alignR"><!--{$arrOrder.deliv_fee|number_format}-->円</td>
            </tr>
            <tr>
                <th colspan="4" class="alignR" scope="row">手数料</th>
                <td class="alignR"><!--{$arrOrder.charge|number_format}-->円</td>
            </tr>
            <tr>
                <th colspan="4" class="alignR" scope="row">合計</th>
                <td class="alignR"><span class="price"><!--{$arrOrder.payment_total|number_format}-->円</span></td>
            </tr>
        </table>

        <!--お届け先ここから-->
        <!--{* 販売方法判定（ダウンロード販売のみの場合はお届け先を表示しない） *}-->
        <!--{if $has_download == false}-->
        <h3>お届け先</h3>
        <table summary="お届け先確認" class="delivname">
            <colgroup width="30%"></colgroup>
            <colgroup width="70%"></colgroup>
            <tbody>
                <tr>
                    <th scope="row">お名前</th>
                    <td><!--{$arrShipping.shipping_name01|h}--> <!--{$arrShipping.shipping_name02|h}--></td>
                </tr>
                <tr>
                    <th scope="row">郵便番号</th>
                    <td>〒<!--{$arrShipping.shipping_zip01|h}-->-<!--{$arrShipping.shipping_zip02|h}--></td>
                </tr>
                <tr>
                    <th scope="row">住所</th>
                    <td><!--{$arrPref[$arrShipping.shipping_pref]}--><!--{$arrShipping.shipping_addr01|h}--><!--{$arrShipping.shipping_addr02|h}--></td>
                </tr>
            </tbody>
        </table>
        <!--{/if}-->
        <!--お届け先ここまで-->

        <h3>配送方法・お支払方法・その他お問い合わせ</h3>
        <table summary="配送方法・お支払方法・その他お問い合わせ" class="delivname">
            <colgroup width="30%"></colgroup>
            <colgroup width="70%"></colgroup>
            <tbody>
            <tr>
                <th scope="row">配送方法</th>
                <td><!--{$arrDeliv[$arrOrder.deliv_id]|h}--></td>
            </tr>
            <tr>
                <th scope="row">お支払方法</th>
                <td><!--{$arrOrder.payment_method|h}--></td>
            </tr>
            <tr>
                <th scope="row">その他お問い合わせ</th>
                <td><!--{$arrOrder.message|h|nl2br}--></td>
            </tr>
            </tbody>
        </table>

        <div class="btn_area">
           <p><input type="submit" value="ご注文完了ページへ" class="spbtn spbtn-shopping" width="130" height="30" alt="ご注文完了ページへ" name="next" id="next" onclick="return fnCheckSubmit();" /></p>
           <p><a href="javascript:;" onclick="fnFormModeSubmit('form1', 'return', '', ''); return false;" class="spbtn spbtn-medeum">戻る</a></p>
        </div>
        </form>
    </div>
</div>
<!--▲CONTENTS-->
