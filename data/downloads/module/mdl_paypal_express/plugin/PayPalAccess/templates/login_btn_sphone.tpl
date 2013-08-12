<!--{if !$paypal_access_authorization}-->
<script type="text/javascript">//<![CDATA[
    $(function() {
        $('form').eq(0).after($('#paypal_access_login').show());
    });
//]]></script>
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
            <a href="<!--{$smarty.const.HTTPS_URL}-->plugin/PayPalAccess/"><img src="https://www.paypalobjects.com/en_US/Marketing/i/btn/login-with-paypal-button.png" /></a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!--{/if}-->
