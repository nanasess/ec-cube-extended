<tr>
    <th>カゴ落ち会員の検索<br /><!--{if $tpl_mainpage == 'mail/index.tpl'}--><span class="attention">HTMLメールのみ配信可能</span><!--{/if}--></th>
    <td colspan="3">
      <script type="text/javascript">//<![CDATA[
          $(function() {
              var interval = '<!--{$smarty.const.DROPPED_ITEMS_NOTICER_SEARCH_INTERVAL}-->'
                , ms = 24 * 60 * 60 * 1000
                , endDate = new Date()
                , startDate = new Date(endDate.getTime() - (interval * ms));

              <!--{if $tpl_mainpage == 'mail/index.tpl'}-->
              // メール管理では <!--{$smarty.const.DROPPED_ITEMS_NOTICER_SEARCH_INTERVAL}--> の範囲のみ表示
              $('select[name^=search_dropped]').children().each(function() {
                  if ($(this).val()) {
                      $(this).remove();
                  }
              });
              var year = 0
                , month = 0
                , day = 0;
              for (var st = startDate.getTime(); st <= endDate.getTime(); st += ms) {
                  var d = new Date(st);
                  if (year < d.getFullYear()) {
                      year = d.getFullYear();
                      var y = '<option value="' + year + '">' + year + '</option>';
                      $('select[name=search_dropped_start_year]').append(y);
                      $('select[name=search_dropped_end_year]').append(y);

                      $('select[name=search_dropped_start_year]').val('<!--{$arrForm.search_dropped_start_year.value|h}-->');
                      $('select[name=search_dropped_end_year]').val('<!--{$arrForm.search_dropped_end_year.value|h}-->');
                  }

                  if (month < d.getMonth() + 1) {
                      month = d.getMonth() + 1;
                      var m = '<option value="' + month + '">' + month + '</option>';
                      $('select[name=search_dropped_start_month]').append(m);
                      $('select[name=search_dropped_end_month]').append(m);

                      $('select[name=search_dropped_start_month]').val('<!--{$arrForm.search_dropped_start_month.value|h}-->');
                      $('select[name=search_dropped_end_month]').val('<!--{$arrForm.search_dropped_end_month.value|h}-->');
                  }
                  if (day < d.getDate()) {
                      day = d.getDate();
                      var d = '<option value="' + day + '">' + day + '</option>';
                      $('select[name=search_dropped_start_day]').append(d);
                      $('select[name=search_dropped_end_day]').append(d);

                      $('select[name=search_dropped_start_day]').val('<!--{$arrForm.search_dropped_start_day.value|h}-->');
                      $('select[name=search_dropped_end_day]').val('<!--{$arrForm.search_dropped_end_day.value|h}-->');
                  }
              }
              <!--{/if}-->
              $('#set_to_date').click(function() {
                  $('select[name=search_dropped_start_year]').val(startDate.getFullYear());
                  $('select[name=search_dropped_start_month]').val(startDate.getMonth() + 1);
                  $('select[name=search_dropped_start_day]').val(startDate.getDate());
                  $('select[name=search_dropped_end_year]').val(endDate.getFullYear());
                  $('select[name=search_dropped_end_month]').val(endDate.getMonth() + 1);
                  $('select[name=search_dropped_end_day]').val(endDate.getDate());
              });
          });
      //]]></script>
    <!--{assign var=errkey1 value="search_dropped_start_year"}-->
    <!--{assign var=errkey2 value="search_dropped_end_year"}-->
        <!--{if $arrErr[$errkey1] || $arrErr[$errkey2]}--><span class="attention"><!--{$arrErr[$errkey1]}--><!--{$arrErr[$errkey2]}--></span><br /><!--{/if}-->
        <!--{assign var=key value="search_dropped_start_year"}-->
        <select name="<!--{$key}-->" <!--{if $arrErr[$errkey1] || $arrErr[$errkey2]}--><!--{sfSetErrorStyle}--><!--{/if}-->>
            <option value="" selected="selected">----</option>
            <!--{html_options options=$arrRegistYear selected=$arrForm[$key].value}-->
        </select>年
        <!--{assign var=key value="search_dropped_start_month"}-->
        <select name="<!--{$key}-->" <!--{if $arrErr[$errkey1] || $arrErr[$errkey2]}--><!--{sfSetErrorStyle}--><!--{/if}-->>
            <option value="" selected="selected">--</option>
            <!--{html_options options=$arrMonth selected=$arrForm[$key].value}-->
        </select>月
        <!--{assign var=key value="search_dropped_start_day"}-->
        <select name="<!--{$key}-->" <!--{if $arrErr[$errkey1] || $arrErr[$errkey2]}--><!--{sfSetErrorStyle}--><!--{/if}-->>
            <option value="" selected="selected">--</option>
            <!--{html_options options=$arrDay selected=$arrForm[$key].value}-->
        </select>日～
        <!--{assign var=key value="search_dropped_end_year"}-->
        <select name="<!--{$key}-->" <!--{if $arrErr[$errkey1] || $arrErr[$errkey2]}--><!--{sfSetErrorStyle}--><!--{/if}-->>
            <option value="" selected="selected">----</option>
            <!--{html_options options=$arrRegistYear selected=$arrForm[$key].value}-->
        </select>年
        <!--{assign var=key value="search_dropped_end_month"}-->
        <select name="<!--{$key}-->" <!--{if $arrErr[$errkey1] || $arrErr[$errkey2]}--><!--{sfSetErrorStyle}--><!--{/if}-->>
            <option value="" selected="selected">--</option>
            <!--{html_options options=$arrMonth selected=$arrForm[$key].value}-->
        </select>月
        <!--{assign var=key value="search_dropped_end_day"}-->
        <select name="<!--{$key}-->" <!--{if $arrErr[$errkey1] || $arrErr[$errkey2]}--><!--{sfSetErrorStyle}--><!--{/if}-->>
            <option value="" selected="selected">--</option>
            <!--{html_options options=$arrDay selected=$arrForm[$key].value}-->
        </select>日

        <a href="javascript:;" id="set_to_date"><!--{$smarty.const.DROPPED_ITEMS_NOTICER_SEARCH_INTERVAL}-->日前より本日までの日付を設定</a>
    </td>
</tr>
