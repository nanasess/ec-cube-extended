<script src="https://www.paypalobjects.com/js/external/api.js"></script>
<script>
paypal.use( ["login"], function(login) {
  login.render ({
    <!--{if $arrPayPalAccessConfig.use_sandbox == '1'}-->"authend": "sandbox",<!--{/if}-->
    "appid": "<!--{$arrPayPalAccessConfig.app_id|h}-->",
    "scopes": "id_token profile email address phone https://uri.paypal.com/services/paypalattributes",
    "containerid": "paypalaccess",
    "locale": "ja-jp",
    "returnurl": "<!--{$smarty.const.HTTPS_URL}-->plugin/PayPalAccess/"
  });
});
</script>
