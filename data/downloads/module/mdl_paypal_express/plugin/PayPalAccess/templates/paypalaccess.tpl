<script src="https://www.paypalobjects.com/js/external/api.js"></script>
<script>
paypal.use( ["login"], function(login) {
  login.render ({
    "appid": "AQAkZBDY6wOQAbnscaKORCHqawapzrO13ffnosEyxQ3wuwmZPUszYoaSY5r3",
    "scopes": "id_token profile email address phone https://uri.paypal.com/services/paypalattributes",
    "containerid": "paypalaccess",
    "locale": "ja-jp",
    "returnurl": "http://version-213.k-4.local/plugin/PayPalAccess/"
  });
});
</script>

<div class="block_outer bloc_outer">
  <div id="paypalaccess" style="text-align: center; width: 150px; margin-left: auto; margin-right: auto;"></div>
</div>
