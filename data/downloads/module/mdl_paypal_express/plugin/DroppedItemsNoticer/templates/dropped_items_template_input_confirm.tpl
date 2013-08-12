<link rel="stylesheet" href="<!--{$smarty.const.ROOT_URLPATH}-->js/jquery.fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />
<script type="text/javascript" src="<!--{$smarty.const.ROOT_URLPATH}-->js/jquery.fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
<script type="text/javascript" src="<!--{$smarty.const.ROOT_URLPATH}-->js/jquery.fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript">//<![CDATA[
    $(function() {
        $('#execute_batch').click(function() {
            doExecuteBatch();
        });
        $('#wrap_loading').hide();
        $('#preview').click(function() {
            var preview = window.open($('#form1').attr('action'), 'preview', "width=650,height=700,scrollbars=yes,resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no");
            $('#form1').attr('target', 'preview');
            $('input[name=mode]').val('preview');
            $('#form1')[0].submit();
            preview.focus();
        });
    });
    function doExecuteBatch() {
        $('#execute_batch').attr('disabled', true);
        $.fancybox.showActivity();
        $('#wrap_loading').height($(document).height())
        $('#wrap_loading').fadeTo('fast', '0.3');

        var postData = {};
        $('input[name^=search_]').each(function() {
            postData[$(this).attr('name')] = $(this).val();
        });
        postData['mode'] = 'dropped_query';
        postData['<!--{$smarty.const.TRANSACTION_ID_NAME}-->'] = '<!--{$transactionid}-->';
        $.ajax({
            type : 'POST',
            cache : false,
            url :  "<!--{$smarty.server.REQUEST_URI|h}-->",
            data : postData,
            dataType : 'json',
            complete : function() {
                $.fancybox.hideActivity();
                $('div#wrap_loading').fadeOut();
                fnFormModeSubmit('form1', 'search', '', '');
            },
            error : function(XMLHttpRequest, textStatus, errorThrown) {
                alert("エラーが発生しました\n" + textStatus);
            },
            success : function(data, textStatus, jqXHR){
                alert(data['result']);
            }
        });
    }
//]]>
</script>
<style type="text/css">
    #wrap_loading {
        position: absolute;
        width: 100%;
        height: 100%;
        top:0 ;
        left: 0;
        z-index: 9999;
        background-color: #000;
    }
</style>
<form name="form1" id="form1" method="post" action="?">
<input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
<input type="hidden" name="mode" value="register" />
<!--{foreach key=key item=item from=$arrHidden}-->
    <!--{if is_array($item)}-->
        <!--{foreach item=c_item from=$item}-->
            <input type="hidden" name="<!--{$key}-->[]" value="<!--{$c_item|h}-->" />
        <!--{/foreach}-->
    <!--{else}-->
        <input type="hidden" name="<!--{$key}-->" value="<!--{$item|h}-->" />
    <!--{/if}-->
<!--{/foreach}-->
<!--{if $arrErr.err}-->
  <span class="attention"><!--{$arrErr.err}--></span>
<!--{/if}-->
<div id="basis" class="contents-main">
    <table>
        <tr>
            <th>メールタイトル<span class="attention"> *</span></th>
            <td>
              <!--{$subject|h}-->
            </td>
        </tr>
        <tr>
            <th>ヘッダー</th>
            <td>
              <pre><!--{$header|h}--></pre>
            </td>
        </tr>
        <tr>
            <td colspan="2"><a href="javascript:;" id="preview">HTMLで確認</a>
        </tr>
        <tr>
            <th>フッター</th>
            <td>
              <pre><!--{$footer|h}--></pre>
            </td>
        </tr>
    </table>

    <div class="btn-area">
        <ul>
            <li><a class="btn-action" href="javascript:;" onclick="fnFormModeSubmit('form1', 'search', '', ''); return false;"><span class="btn-prev">検索画面に戻る</span></a></li>
            <li><a class="btn-action" href="javascript:;" id="execute_batch"><span class="btn-next">配信する</span></a></li>
        </ul>
    </div>
</div>
</form>
<div id="wrap_loading"></div>
