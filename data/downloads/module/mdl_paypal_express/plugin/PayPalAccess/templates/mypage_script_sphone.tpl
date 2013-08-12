<script type="text/javascript" src="<!--{$smarty.const.PLUGIN_HTML_URLPATH}-->PayPalAccess/jquery.paypalaccess_util.js"></script>
<script type="text/javascript">//<![CDATA[
$(function() {
    $('dl.form_entry').paypalaccess_util({
        transactionid: '<!--{$transactionid}-->',
        url: '<!--{$smarty.const.PLUGIN_HTML_URLPATH}-->PayPalAccess/',
        items : '<dt>PayPal アカウントとリンク</dt><dd>{email} の PayPal アカウントとリンクしています。(<a href="javascript:paypalaccount_unlink()">リンク解除</a>)<br />{required_check}</dd>',
        paypal_btn : '<dt>PayPal アカウントとリンク</dt><dd><a href="{url}"><img src="https://www.paypalobjects.com/en_US/Marketing/i/btn/login-with-paypal-button.png" /></a><br />PayPal アカウントを利用して、簡単・安全にログインできます。<br />PayPal アカウントの情報でお買い物できますので、会員情報入力の手間が省けます。</dd>'

    });
});
//]]></script>
