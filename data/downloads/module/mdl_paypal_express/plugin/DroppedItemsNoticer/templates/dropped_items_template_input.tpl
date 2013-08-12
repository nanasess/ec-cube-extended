<form name="form1" id="form1" method="post" action="?">
<input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
<input type="hidden" name="mode" value="register" />
<!--{foreach key=key item=item from=$arrHidden}-->
    <!--{if is_array($item)}-->
        <!--{foreach item=c_item from=$item}-->
            <input type="hidden" name="<!--{$key}-->[]" value="<!--{$c_item|h}-->" />
        <!--{/foreach}-->
    <!--{else}-->
        <input type="hidden" name="<!--{$key}-->" value="<!--{$item|h}-->" />
    <!--{/if}-->
<!--{/foreach}-->
<!--{if $arrErr.err}-->
  <span class="attention"><!--{$arrErr.err}--></span>
<!--{/if}-->
<div id="basis" class="contents-main">
  サイトのソースを開き、ヘッダー及びフッター部分のソースを貼り付けてください。
    <table>
        <tr>
            <th>メールタイトル<span class="attention"> *</span></th>
            <td>
            <!--{assign var=key value="subject"}-->
            <!--{if $arrErr[$key]}-->
            <span class="attention"><!--{$arrErr[$key]}--></span>
            <!--{/if}-->
            <input type="text" name="subject" value="<!--{$arrForm[$key].value|h}-->" size="30" class="box30" style="<!--{$arrErr[$key]|sfGetErrorColor}-->" />
            </td>
        </tr>
        <tr>
            <th>ヘッダー</th>
            <td>
            <!--{assign var=key value="header"}-->
            <!--{if $arrErr[$key]}-->
            <span class="attention"><!--{$arrErr[$key]}--></span>
            <!--{/if}-->
            <textarea name="header" cols="75" rows="12" class="area75"  style="<!--{$arrErr[$key]|sfGetErrorColor}-->"><!--{"\n"}--><!--{$arrForm[$key].value|h}--></textarea>
            </td>
        </tr>
        <tr>
            <th colspan="2" align="center">カゴ落ち商品データ挿入部分</th>
        </tr>
        <tr>
            <th>フッター</th>
            <td>
            <!--{assign var=key value="footer"}-->
            <!--{if $arrErr[$key]}-->
            <span class="attention"><!--{$arrErr[$key]}--></span>
            <!--{/if}-->
            <textarea name="footer" cols="75" rows="12" class="area75" style="<!--{$arrErr[$key]|sfGetErrorColor}-->"><!--{"\n"}--><!--{$arrForm[$key].value|h}--></textarea>
            </td>
        </tr>
    </table>

    <div class="btn-area">
        <ul>
            <li><a class="btn-action" href="javascript:;" onclick="fnFormModeSubmit('form1', 'register', '', ''); return false;"><span class="btn-next">この内容で登録する</span></a></li>
        </ul>
    </div>
</div>
</form>
