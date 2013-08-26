<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_header.tpl"}-->
<style type="text/css">

ol {
   margin-left: 0 !important;
}
ol>li {
    list-style-type: none !important;
    margin: 0 !important;
}
ul.guide>li {
    list-style-type: none !important;
    margin: 0 !important;
}

li img.no {
    padding-right: 5px;
}

body {
	color: #3F4552;
	font-size: 12px;
}

td.pad7 {
	padding: 7px;
}

p.padT3 {
	padding-top: 3px;
}

th,td {
	line-height: 150%;
}

.alignC {
	text-align: center;
}

li img.no {
	vertical-align: middle;
}

ol li {
	padding-bottom: 5px;
}

table.form th {
    padding: 7px;
	width: 180px;
}

.btnArea {
	margin-bottom: 37px;
}

.padB20 {
    padding-bottom: 20px;
}

</style>
<script type="text/javascript">//<![CDATA[
self.moveTo(100,100);
self.resizeTo(640, 750);
self.focus();

if (window.addEventListener) {
    event_listener = window.addEventListener;
    event_name = 'message';
} else {
    event_listener = window.attachEvent;
    event_name = 'onmessage';
}

event_listener(
    event_name,
    function(e) {
        location.hash = '';
        var allow = document.createElement("a");
        allow.setAttribute("href", "<!--{$smarty.const.OSTORE_SSLURL}-->");
        var origin = document.createElement("a");
        origin.setAttribute("href", e.origin);
        if (origin.host == allow.host) {
            // 資料請求フォームへのカーソル移動
            location.hash = e.data
        }
    },
    false
);

function getApiSignature() {
    var url = '';
    if ($('input[name=use_sandbox]').attr('checked')) {
        url = '<!--{$smarty.const.PAYPAL_SANDBOX_API_SIGNATURE_URL}-->';
    } else {
        url = '<!--{$smarty.const.PAYPAL_API_SIGNATURE_URL}-->';
    }

    window.open(url, 'signature', 'width=360, height=500');
}

$(function() {
    $('#devportal_guide').click(function() {
        $('.guide').slideToggle();
    });
    if ($('input[name=use_paypalaccess]').attr('checked')) {
        $('.paypalaccess').show();
    } else {
        $('.paypalaccess').hide();
    }
    $('input[name=use_paypalaccess]').change(function() {
        if ($('input[name=use_paypalaccess]').attr('checked')) {
            $('.paypalaccess').fadeIn('fast');
            $('.paypalaccess').find('input').attr('disabled', false);
        } else {
            $('.paypalaccess').fadeOut('fast');
            $('.paypalaccess').find('input').attr('disabled', true);
        }
    });
    // カゴ落ち通知メルマガ配信機能を有効にする場合は、PayPalでチェックアウトボタンも有効にする
    $('input[name=use_droppeditemsnoticer]').change(function() {
        if ($('input[name=use_droppeditemsnoticer]').attr('checked')) {
            if (!$('input[name=use_express_btn]').attr('checked')) {
                alert('カゴ落ち通知メルマガ配信機能を有効にする場合は、PayPalでチェックアウトボタンも有効になります。');
                $('input[name=use_express_btn]').attr('checked', true);
            }
            $('input[name=use_express_btn]').attr('disabled', true);
            $('<input type="hidden" name="use_express_btn" value="1" id="use_express_hidden" />').appendTo('#form1');
        } else {
            $('input[name=use_express_btn]').attr('disabled', false);
            $('#use_express_hidden').remove();
        }
    });
});

//]]>
</script>

<iframe src="<!--{$smarty.const.OSTORE_SSLURL}-->paypal_info/set01.php"
        style="width:564px;height:320px;margin-bottom: 18px;"
        scrolling="no"
        marginwidth="0"
        marginheight="0"
        frameborder="0"></iframe>

<form name="form1" id="form1" method="post" action="<!--{$smarty.server.REQUEST_URI|escape}-->">
<input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
<input type="hidden" name="mode" value="edit">

<table class="form">
  <colgroup width="20%"></colgroup>
  <colgroup width="40%"></colgroup>
  <tbody>
  <tr>
    <th>APIユーザー名<span class="attention">※</span></th>
    <td class="pad7">
      <!--{assign var=key value="api_user"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="text" name="<!--{$key}-->" style="ime-mode:disabled; <!--{$arrErr[$key]|sfGetErrorColor}-->" value="<!--{$arrForm[$key].value}-->" class="box20" maxlength="<!--{$arrForm[$key].length}-->" />
      <p>API署名の情報は<a href="javascript:;" onclick="getApiSignature(); return false;">こちら</a>から取得可能です</p>
    </td>
  </tr>
  <tr>
    <th>APIパスワード<span class="attention">※</span></th>
    <td class="pad7">
      <!--{assign var=key value="api_pass"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="password" name="<!--{$key}-->" style="ime-mode:disabled; <!--{$arrErr[$key]|sfGetErrorColor}-->" value="<!--{$arrForm[$key].value}-->" class="box20" maxlength="<!--{$arrForm[$key].length}-->" />
    </td>
  </tr>
  <tr>
    <th>API署名<span class="attention">※</span></th>
    <td class="pad7">
      <!--{assign var=key value="api_signature"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="text" name="<!--{$key}-->" style="ime-mode:disabled; <!--{$arrErr[$key]|sfGetErrorColor}-->" value="<!--{$arrForm[$key].value}-->" class="box40" maxlength="<!--{$arrForm[$key].length}-->" />
    </td>
  </tr>
  <tr>
    <th>
      「PayPal でチェックアウト」<br />ボタンの使用
       <p class="alignC"><img src="https://www.paypal.com/ja_JP/i/btn/btn_xpressCheckout.gif" alt="PayPal Express Checkout ボタン" align="center"></p>
    </th>
    <td>
      <!--{assign var=key value="use_express_btn"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="checkbox" name="<!--{$key}-->" value="1" id="<!--{$key}-->" <!--{if $arrForm[$key].value == 1}-->checked="checked"<!--{/if}--> /><label for="use_express_btn">「PayPalでチェックアウト」ボタンを使用する</label>
      <p class="fs14 padT3">※ 購入時に「PayPal でチェックアウト」ボタンをカート画面に表示させる場合はチェックを入れて下さい。顧客は PayPal でお支払い方法と配送先を選択しますので、スムーズに購入を完了できます。</p>
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
  <!--{if $paypalaccess_flg == true}-->
  <tr>
    <th>Log In with PayPal の使用<br/>
        <p class="alignC" id="paypalaccess"></p>
    </th>
    <td class="pad7">
      <!--{if $check_ssl == true}-->
      <!--{assign var=key value="use_paypalaccess"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="checkbox" name="<!--{$key}-->" value="1" id="<!--{$key}-->" <!--{if $arrForm[$key].value == 1}-->checked="checked"<!--{/if}--> /><label for="use_paypalaccess">Log In with PayPal を使用する</label>
      <!--{else}-->
          <div class="attention">SSL が無効になっています。Log In with PayPal をご利用の際は、必ず SSL をご使用ください。(HTTPS_URL パラメータが https:// ではありません。)</div>
      <!--{/if}-->
      <p class="fs14 padT3 padB20">※Log In with PayPal プラグインをご利用頂く為には、PayPal DevPortal の登録が必要です。以下の手順で登録の上、ご利用ください。</p>
      <ol>
        <li><img class="no" src="<!--{$smarty.const.OSTORE_SSLURL}-->user_data/packages/default/img/paypal_info/ico01.jpg" alt="1"><a href="https://devportal.x.com" target="_blank">https://devportal.x.com</a> へアクセスします。</li>
        <li><img class="no" src="<!--{$smarty.const.OSTORE_SSLURL}-->user_data/packages/default/img/paypal_info/ico02.jpg" alt="2">PayPal アカウントでログインします。</li>
        <li><img class="no" src="<!--{$smarty.const.OSTORE_SSLURL}-->user_data/packages/default/img/paypal_info/ico03.jpg" alt="3">「Manage Applications」ボタンをクリックします。</li>
        <li><img class="no" src="<!--{$smarty.const.OSTORE_SSLURL}-->user_data/packages/default/img/paypal_info/ico04.jpg" alt="4">必要事項を入力し、「Register Application」ボタンをクリックします。<a href="javascript:;" id="devportal_guide">(詳細)</a>
        <ul class="guide" style="display:none">
          <li><strong>Application Name</strong> - Log In with PayPal への登録名を入力します。(必須・半角英数字記号)</li>
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
        <li><img class="no" src="<!--{$smarty.const.OSTORE_SSLURL}-->user_data/packages/default/img/paypal_info/ico05.jpg" alt="5">画面に表示された App ID 及び App Secret を以下のフォームに入力し、「登録」ボタンをクリックします。</li>
      </ol>
    </td>
  </tr>
  <tr class="paypalaccess">
    <th>App ID<span class="attention">※</span></th>
    <td class="pad7">
      <!--{assign var=key value="app_id"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="text" name="<!--{$key}-->" style="ime-mode:disabled; <!--{$arrErr[$key]|sfGetErrorColor}-->" value="<!--{$arrForm[$key].value}-->" class="box20" maxlength="<!--{$arrForm[$key].length}-->" />
    </td>
  </tr>
  <tr class="paypalaccess">
    <th>App Secret<span class="attention">※</span></th>
    <td class="pad7">
      <!--{assign var=key value="app_secret"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="password" name="<!--{$key}-->" style="ime-mode:disabled; <!--{$arrErr[$key]|sfGetErrorColor}-->" value="<!--{$arrForm[$key].value}-->" class="box20" maxlength="<!--{$arrForm[$key].length}-->" />
    </td>
  </tr>
  <tr class="paypalaccess">
    <th>
      「カナ(姓/名)・性別」の入力
    </th>
    <td class="pad7">
      <!--{assign var=key value="requires_revoke"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="radio" name="<!--{$key}-->" value="1" id="<!--{$key}-->" <!--{if $arrForm[$key].value == 1}-->checked="checked"<!--{/if}--> />任意
      <input type="radio" name="<!--{$key}-->" value="2" id="<!--{$key}-->" <!--{if $arrForm[$key].value == 2}-->checked="checked"<!--{/if}--> />必須
      <p>※ PayPal アカウントから、会員の「カナ(姓/名)・性別」は取得できません。必須にした場合は、Log In with PayPal でログイン後、「カナ(姓/名)・性別」の入力を促します。</p>
    </td>
  </tr>
  <!--{/if}-->
  <!--{if $droppeditemsnoticer_flg == true}-->
  <tr>
    <th>カゴ落ち通知メルマガ配信機能<br />の使用</th>
    <td class="pad7">
      <!--{assign var=key value="use_droppeditemsnoticer"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="checkbox" name="<!--{$key}-->" value="1" id="<!--{$key}-->" <!--{if $arrForm[$key].value == 1}-->checked="checked"<!--{/if}--> /><label for="<!--{$key}-->">カゴ落ち通知メルマガ配信機能を使用する</label>
      <p class="fs14 padT3 padB20">※カゴ落ちした会員を対象にメルマガ配信をする場合はチェックを入れてください。メルマガを受信した会員は、「PayPal でチェックアウト」ボタンを使用して、スムーズに購入を完了できます。以下の手順でご利用ください。</p>
      <ol>
        <li><img class="no" src="<!--{$smarty.const.OSTORE_SSLURL}-->user_data/packages/default/img/paypal_info/ico01.jpg" alt="1">「EC-CUBE 管理画面」 &gt; 「メルマガ管理」 &gt; 「配信内容設定」のカゴ落ち会員の検索に日付を入力して検索します。</li>
        <li><img class="no" src="<!--{$smarty.const.OSTORE_SSLURL}-->user_data/packages/default/img/paypal_info/ico02.jpg" alt="2">「カゴ落ち通知メルマガ配信内容を設定する」にて、内容確認及び配信します。
      </li>
      </ol>
      <p class="padT3 padB20">※メルマガのテンプレートの編集は、「EC-CUBE 管理画面」 &gt; 「メルマガ管理」 &gt; 「カゴ落ち通知メルマガテンプレート管理」より可能です。</p>
    </td>
  </tr>
  <!--{/if}-->
  <tr>
    <th>「PayPalが使えます」バナー<br />の使用</th>
    <td class="pad7">
      <!--{assign var=key value="use_paypal_banner"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      <input type="radio" name="<!--{$key}-->" value="1" id="<!--{$key}-->" <!--{if $arrForm[$key].value == 1}-->checked="checked"<!--{/if}--> />右ナビ
      <input type="radio" name="<!--{$key}-->" value="2" id="<!--{$key}-->" <!--{if $arrForm[$key].value == 2}-->checked="checked"<!--{/if}--> />左ナビ
      <input type="radio" name="<!--{$key}-->" value="0" id="<!--{$key}-->" <!--{if $arrForm[$key].value == 0}-->checked="checked"<!--{/if}--> />使用しない
    </td>
  </tr>
  <tr>
    <th>PayPalログインページの設定</th>
    <td class="pad7">
      <!--{assign var=key value="use_corporate_logo"}-->
      <span class="attention"><!--{$arrErr[$key]}--></span>
      ショップロゴ画像のアップロード 
      <input type="file" name="<!--{$key}-->" value="0" id="<!--{$key}-->"  />
      枠色の設定
    </td>
  </tr>

</table>
<div class="alignC btnArea">
	<a class="" href="javascript:;" onclick="fnFormModeSubmit('form1', 'edit', '', ''); return false;"><img class="no" src="<!--{$smarty.const.OSTORE_SSLURL}-->user_data/packages/default/img/paypal_info/btn_register.jpg" alt="入力した内容で登録する"></a>
</div>
<iframe id="form"
        src="https://f.msgs.jp/webapp/form/15935_xox_114/index.do"
        style="width:561px;height:740px;margin-bottom: 18px;"
        scrolling="no"
        marginwidth="0"
        marginheight="0"
        frameborder="0"></iframe>
</form>
<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_footer.tpl"}-->
