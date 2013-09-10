<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_header.tpl"}-->
<style type="text/css">
ol {
   margin-left: 2em;
}
ol>li {
    list-style-type: decimal;
    margin: 5px;
}
ul.guide>li {
    list-style-type: square;
    margin: 5px;
}
</style>
<script type="text/javascript">//<![CDATA[
self.moveTo(20,20);
self.resizeTo(640, 650);
self.focus();
$(function() {
    $('#devportal_guide').click(function() {
        $('.guide').slideToggle();
    });
});
//]]>
</script>
<h2><!--{$tpl_subtitle}--></h2>
<form name="form1" id="form1" method="post" action="<!--{$smarty.server.REQUEST_URI|escape}-->">
<input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
<input type="hidden" name="mode" value="edit">
<p class="remark">Log In with PayPal プラグインをご利用頂く為には、PayPal Developer の登録が必要です。以下の手順で登録の上、ご利用ください。
<ol>
  <li><a href="https://developer.paypal.com/" target="_blank">https://developer.paypal.com/</a> へアクセスし、PayPal アカウントでログインします。</li>
  <li>「Applications」タブをクリックし、「My apps」の「Create application」をクリックします。</li>
  <li>「Application name」に登録するサイト名(半角英数字記号)、「Integration type」に Web を選択し、「Create Application」をクリックします。</li>
  <li>「LOG IN WITH PAYPAL」の ON/OFF スイッチをクリックし、必要事項を入力します。<a href="javascript:;" id="devportal_guide">(詳細)</a>
  <ul class="guide" style="display:none">
    <li><strong>Information requested from customers</strong> - Personal Information, Address Information, Account Information にチェックを入れます。</li>
    <li><strong>Return URL</strong> - <!--{$smarty.const.HTTPS_URL}-->plugin/<!--{$smarty.const.PAYPAL_ACCESS_PLUGIN_NAME}-->/ を入力します。</li>
    <li><strong>Privacy Policy URL</strong> - <!--{$smarty.const.HTTP_URL}-->guide/privacy.php を入力します。</li>
    <li><strong>User Agreement URL</strong> - <!--{$smarty.const.HTTP_URL}-->entry/kiyaku.php を入力します。</li>
    <li><strong>Use Seamless Checkout</strong> - 商品購入時に再ログインしないようにする場合はチェックを入れます。</li>
    <li><strong>Allow the customers who haven't yet confirmed their email address with PayPal, to log in to your app.</strong> - 認証済みメールアドレスのみ許可する場合はチェックを入れます。</li>
  </ul>
  </li>
  <li>「REST API CREDENTIALS」の Show をクリックし、Live credentials の Client ID 及び Secret を以下のフォームに入力し、「登録」ボタンをクリックします。</li>
</ol>
<a href="https://developer.paypal.com/webapps/developer/docs/integration/direct/log-in-with-paypal/" target="_blank"> ＞＞ Log In with PayPalについて</a><br />
【お問い合わせ先】電話: 03-6739-7135／メール：<a href="mailto:wpp@paypal.com">wpp@paypal.com</a></p>
<!--{if $arrErr.err != ""}-->
    <div class="attention"><!--{$arrErr.err}--></div>
<!--{/if}-->

<table class="form">
  <colgroup width="20%"></colgroup>
  <colgroup width="40%"></colgroup>
  <tr>
    <th>App ID<span class="attention">※</span></th>
    <td>
      <!--{assign var=key value="app_id"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="text" name="<!--{$key}-->" style="ime-mode:disabled; <!--{$arrErr[$key]|sfGetErrorColor}-->" value="<!--{$arrForm[$key].value}-->" class="box20" maxlength="<!--{$arrForm[$key].length}-->" />
    </td>
  </tr>
  <tr>
    <th>App Secret<span class="attention">※</span></th>
    <td>
      <!--{assign var=key value="app_secret"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="password" name="<!--{$key}-->" style="ime-mode:disabled; <!--{$arrErr[$key]|sfGetErrorColor}-->" value="<!--{$arrForm[$key].value}-->" class="box20" maxlength="<!--{$arrForm[$key].length}-->" />
    </td>
  </tr>
  <tr>
    <th>
      「カナ(姓/名)・性別」の入力
    </th>
    <td>
      <!--{assign var=key value="requires_revoke"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="radio" name="<!--{$key}-->" value="1" id="<!--{$key}-->" <!--{if $arrForm[$key].value == 1}-->checked="checked"<!--{/if}--> />任意
      <input type="radio" name="<!--{$key}-->" value="2" id="<!--{$key}-->" <!--{if $arrForm[$key].value == 2}-->checked="checked"<!--{/if}--> />必須
      <p>※ PayPal アカウントから、会員の「カナ(姓/名)・性別」は取得できません。必須にした場合は、Log In with PayPal でログイン後、「カナ(姓/名)・性別」の入力を促します。</p>
    </td>
  </tr>
  <tr>
    <th><a href="https://www.paypal.jp/jp/contents/support/introduction/sandbox/" target="_blank">Sandbox<span class="fs14">(開発用テストツール)</span></a><br />の使用</th>
    <td class="pad7">
      <!--{assign var=key value="use_sandbox"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="checkbox" name="<!--{$key}-->" value="1" id="<!--{$key}-->" <!--{if $arrForm[$key].value == 1}-->checked="checked"<!--{/if}--> /><label for="use_sandbox">Sandboxを使用する</label>
    </td>
  </tr>
</table>
<!--{if $check_ssl}-->
<div class="btn-area">
  <ul>
    <li><a class="btn-action" href="javascript:;" onclick="fnFormModeSubmit('form1', 'edit', '', ''); return false;"><span class="btn-next">登録</span></a></li>
  </ul>
</div>
<!--{else}-->
<div class="attention">SSL が無効になっています。Log In with PayPal をご利用の際は、必ず SSL をご使用ください。(HTTPS_URL パラメータが https:// ではありません。)</div>
<!--{/if}-->
</form>
<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_footer.tpl"}-->
