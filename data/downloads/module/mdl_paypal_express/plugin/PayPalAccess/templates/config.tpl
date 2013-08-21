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
<p class="remark">PayPal Access プラグインをご利用頂く為には、PayPal DevPortal の登録が必要です。以下の手順で登録の上、ご利用ください。
<ol>
  <li><a href="https://devportal.x.com" target="_blank">https://devportal.x.com</a> へアクセスします。</li>
  <li>PayPal アカウントでログインします。</li>
  <li>「Manage Applications」ボタンをクリックします。</li>
  <li>必要事項を入力し、「Register Application」ボタンをクリックします。<a href="javascript:;" id="devportal_guide">(詳細)</a>
     <ul class="guide" style="display:none">
       <li><strong>Application Name</strong> - PayPal Accessへの登録名を入力します。(必須・半角英数字記号)</li>
       <li><strong>Display Name</strong> - 顧客への表示名を入力します。(必須)</li>
       <li><strong>Domain URL</strong> - <!--{$smarty.const.HTTPS_URL}--> を入力します。(必須)</li>
       <li><strong>Contact Email</strong> - 連絡先メールアドレスを入力します。 (必須)</li>
       <li><strong>Privacy Policy URL</strong> - <!--{$smarty.const.HTTP_URL}-->guide/privacy.php を入力します。</li>
       <li><strong>User Agreement URL</strong> - <!--{$smarty.const.HTTP_URL}-->entry/kiyaku.php を入力します。</li>
       <li><strong>Allow only users with verified emails to login</strong> - 認証済みメールアドレスのみ許可する場合はチェックを入れます</li>
       <li><strong>Protocols</strong> - 「OAuth 2.0 / Open Id Connect」にチェックを入れます</li>
       <li><strong>Return URL</strong> - <!--{$smarty.const.HTTPS_URL}-->plugin/<!--{$smarty.const.PAYPAL_ACCESS_PLUGIN_NAME}-->/ を入力します。</li>
       <li><strong>Attributes Level</strong> - Full Name, Date of Birth, Email Address, Street Address, City, State, Zip, Country, Phone Number,Account Verified にチェックを入れます。それ以外は任意です。</li>
       <li><strong>PayPal App ID</strong> - 空欄とします</li>

     </ul>
  </li>
  <li>画面に表示された App ID 及び App Secre を以下のフォームに入力し、「登録」ボタンをクリックします。</li>
</ol>
<a href="https://www.x.com/developers/paypal/products/paypal-access" target="_blank"> ＞＞ PayPal Accessについて</a><br />
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
      <p>※ PayPal アカウントから、会員の「カナ(姓/名)・性別」は取得できません。必須にした場合は、PayPal Access でログイン後、「カナ(姓/名)・性別」の入力を促します。</p>
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
<div class="attention">SSL が無効になっています。PayPal Access をご利用の際は、必ず SSL をご使用ください。(HTTPS_URL パラメータが https:// ではありません。)</div>
<!--{/if}-->
</form>
<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_footer.tpl"}-->
