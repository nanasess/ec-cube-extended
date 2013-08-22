(function($){
    var o
      , _status = function($this) {
            $.ajax({
                url : o.url,
                async : false,
                data : {
                    mode : 'ajax_status',
                    transactionid : o.transactionid
                },
                type : 'post',
                dataType : 'json',
                success: function(data, textStatus, jqXHR) {
                    if (data.email) {
                        o.items = o.items.replace(/{email}/, data.email);
                    } else {
                        o.items = o.paypal_btn.replace(/{url}/, o.url);
                    }
                    if (data.required_check) {
                        o.items = o.items.replace(/{required_check}/, data.required_error);
                    } else {
                        o.items = o.items.replace(/{required_check}/, '');
                    }
                    if (data.requires_revoke == '1') {
                        // 必須項目を解除
                        $this.find('th, dt').each(function() {
                            if ($(this).text().match(/フリガナ/)) {
                                $(this).find('span.attention').remove();
                            }
                            if ($(this).text().match(/性別/)) {
                                $(this).children('span.attention').remove();
                            }
                        });
                    }
                    $this.prepend(o.items);

                    return true;
                },
                error : function(jqXHR, textStatus, errorThrown) {
                    alert('Can\'t get status. : ' + textStatus);
                }
            });
        }
      , _unlink = function() {
            $.ajax({
                url : o.url,
                data : {
                    mode : 'ajax_unlink',
                    transactionid : o.transactionid
                },
                type : 'post',
                dataType : 'json',
                success : function(data, textStatus, jqXHR) {
                    if (data.success) {
                        alert(o.unlink_success);
                        window.location.reload();
                        return true;
                    } else {
                        alert(o.unlink_failure + "\n" + data.error);
                        window.location.reload();
                        return false;
                    }
                },
                error : function(jqXHR, textStatus, errorThrown) {
                    alert('Unlink Error : ' + textStatus);
                }
            });
        };

    $.fn.paypalaccess_util = function(options) {
        return this.each(function() {
                   if (options) {
                       o = $.fn.extend($.fn.paypalaccess_util.defaults, options);
                   } else {
                       o = $.fn.paypalaccess_util.defaults;
                   }
                   var $this = $(this);
                   _status($this);
               });
    };

    $.fn.paypalaccess_util.defaults = {
        items : '<tr><th>PayPal アカウントとリンク</th><td>{email} の PayPal アカウントとリンクしています。(<a href="javascript:paypalaccount_unlink()">リンク解除</a>)<br />{required_check}</td></tr>',
        url : '/plugin/PayPalAccess/',
        transactionid : 'transactionid',
        unlink_success : 'PayPalアカウントとのリンクを解除しました。',
        unlink_failure : 'PayPalアカウントとのリンクを解除できませんでした。',
        paypal_btn : '<tr><th>PayPal アカウントとリンク</th><td><a href="{url}"><img src="https://www.paypalobjects.com/en_US/Marketing/i/btn/login-with-paypal-button.png" /></a><br />PayPal アカウントを利用して、簡単・安全にログインできます。<br />PayPal アカウントの情報でお買い物できますので、会員情報入力の手間が省けます。</td></tr>'
    };

    window.paypalaccount_unlink = function() {
        _unlink();
    };
})(jQuery);
