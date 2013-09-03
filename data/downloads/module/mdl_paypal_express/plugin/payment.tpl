<!--{include file=$include_mainpage}-->
<script type="text/javascript">
$(function() {
    var olcwhatispaypal = function() {
        window.open('https://www.paypal.com/jp/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside',
                    'olcwhatispaypal', 'scrollbars=yes, resizable=yes, width=640, height=480');
    };
    $("img[src$='paypal_payment_logo.gif']")
        .click(olcwhatispaypal)
        .css('cursor', 'pointer')
        .after('<div id="paypal_guide"><a href="javascript:;">ペイパルとは？</a></div>');
    $('#paypal_guide')
        .css({'font-size': '90%',
              'text-align': 'right',
              'margin-right': '5px'
        });
    $('#paypal_guide>a')
        .click(olcwhatispaypal);
});
</script>

