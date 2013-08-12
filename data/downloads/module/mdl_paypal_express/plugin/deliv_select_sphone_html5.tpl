<section id="undercolumn">
    <div id="undercolumn_shopping">
        <h2 class="title"><!--{$tpl_title|h}--></h2>

        <form name="form1" id="form1" method="post" action="<!--{$smarty.server.PHP_SELF|h}-->">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
        <input type="hidden" name="mode" value="do_express" />
        <input type="hidden" name="uniqid" value="<!--{$tpl_uniqid}-->" />
        <input type="hidden" name="cartKey" value="<!--{$cartKey|h}-->" />
        <input type="hidden" name="retry" value="1" />
        <!--{assign var=key value="deliv_id"}-->
        <div class="pay_area">
            <h3 class="subtitle">配送方法の指定</h3>
            <p>配送方法をご選択ください。</p>

            <!--{if $arrErr[$key] != ""}-->
            <p class="attention"><!--{$arrErr[$key]}--></p>
            <!--{/if}-->
                <ul>
                <!--{section name=cnt loop=$arrDelivSelect}-->
                    <li><input type="radio" id="deliv_<!--{$smarty.section.cnt.iteration}-->" name="<!--{$key}-->" value="<!--{$arrDelivSelect[cnt].deliv_id}-->" style="<!--{$arrErr[$key]|sfGetErrorColor}-->" <!--{$arrDelivSelect[cnt].deliv_id|sfGetChecked:$arrForm[$key].value}--> class="data-role-none" />
                    <label for="deliv_<!--{$smarty.section.cnt.iteration}-->"><!--{$arrDelivSelect[cnt].name|h}--><!--{if $arrDelivSelect[cnt].remark != ""}--><p><!--{$arrDelivSelect[cnt].remark|h|nl2br}--></p><!--{/if}--></label>
                    </li>
                <!--{/section}-->
                </ul>
            <div class="btn_area">
              <ul class="btn_btm">

                <li><a rel="external" href="javascript:void(document.form1.submit());" class="btn">次へ</a></li>
                <li><a rel="external" href="<!--{$smarty.const.CART_URLPATH}-->" class="btn_back">戻る</a></li>
            </ul>
          </div>
        </form>
    </div>
</section>
