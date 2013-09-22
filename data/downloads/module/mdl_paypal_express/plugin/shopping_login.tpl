<script>
  $(function() {
      $('.login_area').prepend($('#checkout_with_paypal'));
      $('#checkout_with_paypal').show();
      $('#do_express').click(function() {
          document.formexpress.submit();
      });
  });
</script>
<form name="formexpress" id="formexpress" action="<!--{$smarty.const.ROOT_URLPATH}-->cart/index.php" method="post">
  <input type="hidden" name="mode" value="do_express" />
  <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
  <input type="hidden" name="cartKey" value="<!--{$smarty.const.PRODUCT_TYPE_NORMAL}-->" />
</form>
<div id="checkout_with_paypal" style="display: none">
  <h3>PayPalでチェックアウト</h3>
  <p class="inputtext">「PayPal でチェックアウト」をクリックすると、よりスピーディで、より安全に、お支払いが可能になります。<br />送付先もPayPal画面でご指定ください。</p>
  <div class="inputbox">
    <div class="btn_area">
      <ul>
        <li>
          <a href="javascript:;" id="do_express">
            <img src="<!--{$smarty.const.PAYPAL_EXPRESS_CHECKOUT_BUTTON}-->"  alt="PayPal でチェックアウト" width="145" height="42" />
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>
<!--{include file=$include_mainpage}-->
