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
$(function() {
    $('#submit').click(function() {
        if (fnCheckSubmit()) {
            $('input[name=mode]').val('confirm');
        }
    });
    $('#return').click(function() {
        $('input[name=mode]').val('return');
    });
});
//]]></script>

<!--CONTENTS-->
<section id="undercolumn">
        <h2 class="title"><!--{$tpl_title|h}--></h2>

        <!--{if $tpl_message}-->
        <p class="attention">エラーメッセージ:<br /><!--{$tpl_message}--></p>
        <!--{else}-->
        <p class="information end">PayPal決済画面で指定いただいた住所に送付いたします。<br />
        送料が追加・変更されたため、お支払いいただく金額が変更されている可能性がありますので、ご確認ください。<br />
            よろしければ、「ご注文完了ページへ」ボタンをクリックしてください。</p>
        <!--{/if}-->
        <form name="form1" id="form1" method="post" action="<!--{$smarty.server.PHP_SELF|h}-->" data-ajax=“false”>
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
        <input type="hidden" name="mode" value="confirm" />
        <input type="hidden" name="uniqid" value="<!--{$tpl_uniqid}-->" />

        <h3 class="subtitle">ご注文内容</h3>
        <section class="cartconfirm_area">
            <div class="form_area">
                <div class="formBox">
                    <div class="cartcartconfirmarea">
                        <!--{foreach from=$arrOrderDetails item=item}-->
                            <!--▼商品 -->
                            <div class="cartconfirmBox">
                                <img src="<!--{$smarty.const.ROOT_URLPATH}-->resize_image.php?image=<!--{$item.productsClass.main_list_image|sfNoImageMainList|h}-->&amp;width=80&amp;height=80" alt="<!--{$item.productsClass.name|h}-->" width="80" height="80" class="photoL" />
                                 <div class="cartconfirmContents"><div>
                                 <p><em><!--{$item.productsClass.name|h}--></em><br />
                                 <!--{if $item.productsClass.classcategory_name1 != ""}-->
                                     <span class="mini"><!--{$item.productsClass.class_name1}-->：<!--{$item.productsClass.classcategory_name1}--></span><br />
                                 <!--{/if}-->
                                 <!--{if $item.productsClass.classcategory_name2 != ""}-->
                                     <span class="mini"><!--{$item.productsClass.class_name2}-->：<!--{$item.productsClass.classcategory_name2}--></span>
                                 <!--{/if}-->
                                  </p>
                             </div>
                             <ul>
                                 <li><span class="mini">数量：</span><!--{$item.quantity|number_format}--></li>
                                 <li class="result"><span class="mini">小計：</span><!--{$item.price|sfCalcIncTax:$arrInfo.tax:$arrInfo.tax_rule|number_format}-->円</li>
                             </ul>
                         </div>
                      </div>
                             <!--▲商品 -->
                         <!--{/foreach}-->
                    </div>

                     <!--★合計内訳★-->
                    <div class="result_area">
                        <ul>
                        <li><span class="mini">小計 ：</span><!--{$arrOrder.subtotal|number_format}--> 円</li>
                        <!--{if $smarty.const.USE_POINT !== false}-->
                            <li><span class="mini">値引き（ポイントご使用時）： </span><!--{assign var=discount value=`$arrOrder.use_point*$smarty.const.POINT_VALUE`}-->
        -<!--{$discount|number_format|default:0}--> 円</li>
                        <!--{/if}-->
                        <li><span class="mini">送料 ：</span><!--{$arrOrder.deliv_fee|number_format}--> 円</li>
                        <li><span class="mini">手数料 ：</span><!--{$arrOrder.charge|number_format}--> 円</li>
                        </ul>
                     </div>

                     <!--★合計★-->
                     <div class="total_area">
                         <span class="mini">合計：</span><span class="price fb"><!--{$arrOrder.payment_total|number_format}--> 円</span>
                     </div>
                </div><!--▲formBox -->
          </div>

          <!--お届け先ここから-->
          <!--{* 販売方法判定（ダウンロード販売のみの場合はお届け先を表示しない） *}-->
          <!--{if $has_download == false}-->
          <section class="delivconfirm_area">
          <h3 class="subtitle">お届け先</h3>

          <div class="form_area">
              <div class="formBox">
                  <dl class="deliv_confirm">
                      <dt>お届け先</dt>
                      <dd>
                          <p>〒<!--{$arrShipping.shipping_zip01|h}-->-<!--{$arrShipping.shipping_zip02|h}--><br />
                          <!--{$arrPref[$arrShipping.shipping_pref]}--><!--{$arrShipping.shipping_addr01|h}--><!--{$arrShipping.shipping_addr02|h}--></p>
                          <p class="deliv_name"><!--{$arrShipping.shipping_name01|h}--> <!--{$arrShipping.shipping_name02|h}--></p>
                          <p><!--{$arrShipping.shipping_tel01}-->-<!--{$arrShipping.shipping_tel02}-->-<!--{$arrShipping.shipping_tel03}--></p>
                      </dd>
                   </dl>
               </div>
           </div>
           </section>
        <!--{/if}-->
        <!--お届け先ここまで-->

        <section class="otherconfirm_area">
        <h3 class="subtitle">配送方法・お支払方法など</h3>
        <div class="form_area">
        <!--▼フォームボックスここから -->
            <div class="formBox">
                <div class="innerBox">
                    <em>配送方法</em>：<!--{$arrDeliv[$arrOrder.deliv_id]|h}-->
                </div>
                <div class="innerBox">
                    <em>お支払方法：</em><!--{$arrOrder.payment_method|h}-->
                </div>
                <div class="innerBox">
                    <em>その他お問い合わせ：</em><br />
                    <!--{$arrOrder.message|h|nl2br}-->
               </div>
            </div><!--▲formBox -->
       </div><!--▲form_area -->
       </section>

       <!--★ボタン★-->
       <div class="btn_area">
           <ul class="btn_btm">
               <li><a rel="external" href="javascript:void(document.form1.submit());" id="submit" class="btn">ご注文完了ページへ</a></li>
               <li><a rel="external" href="javascript:void(document.form1.submit());" id="return" class="btn_back">戻る</a></li>
           </ul>
       </div>
      </form>
</section>
<!--▲CONTENTS-->
