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

<div class="block_outer bloc_outer">
  <div id="paypalaccess" style="text-align: center; width: 150px; margin-left: auto; margin-right: auto;"></div>
</div>
