<!--{if !$paypal_access_authorization}-->
<script type="text/javascript">//<![CDATA[
    $(function() {
        $('#member_form').eq(0).after($('#paypal_access_login').show());
    });
//]]></script>
<script src="https://www.paypalobjects.com/js/external/api.js"></script>
<script>
paypal.use( ["login"], function(login) {
  login.render ({
    "authend": "sandbox",
    "appid": "AZ52ABDdmAefYyQU11B7zvEqnRKn6qQBMfMGOitKRw47THBS1rdhYRzWArJE",
    "scopes": "id_token profile email address phone https://uri.paypal.com/services/paypalattributes",
    "containerid": "paypalaccess",
    "locale": "ja-jp",
    "returnurl": "<!--{$smarty.const.HTTPS_URL}-->plugin/PayPalAccess/"
  });
});
</script>
<div id="paypal_access_login" style="display: none">
  <div class="login_area">
    <h3>PayPal アカウントでログイン</h3>
    <p class="inputtext">PayPal アカウントを利用して、簡単・安全にログインできます。<br />
    PayPal アカウントの情報でお買い物できますので、会員情報入力の手間が省けます。
    </p>
    <div class="inputbox">
      <div class="btn_area">
        <ul>
          <li>
            <div id="paypalaccess" style="text-align: center; width: 150px; margin-left: auto; margin-right: auto;"></div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!--{/if}-->
