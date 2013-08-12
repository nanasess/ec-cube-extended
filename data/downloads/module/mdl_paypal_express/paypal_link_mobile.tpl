<center><font color="red">この決済は携帯に対応していません</font></center>
<form action="./load_payment_module.php" method="post">
<input type="hidden" name="mode" value="return">
<input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
<center><input type="submit" value="戻る"></center>
</form>
