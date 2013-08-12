<div>
  <h2>カゴ落ち商品一覧</h2>
  <!--{if $tpl_dropped_linemax > 0}-->
  <p><span class="attention"><!--カゴ落ち商品一覧--><!--{$tpl_dropped_linemax}-->件</span>&nbsp;が該当しました。</p>

            <!--{* カゴ落ち商品一覧表示テーブル *}-->
            <table class="list">
                <tr>
                    <th>日付</th>
                    <th>商品ID</th>
                    <th>商品コード</th>
                    <th>商品名/(規格1)/(規格2)</th>
                    <th>単価</th>
                    <th>数量</th>
                </tr>
                <!--{foreach name=dropped from=$arrDroppedHistory item=dropped}-->
                    <tr>
                        <td><!--{$dropped.dropped_date|sfDispDBDate}--></td>
                        <td class="center"><!--{$dropped.productsClass.product_id|h}--></td>
                        <td class="center"><!--{$dropped.productsClass.product_code|h}--></td>
                        <td class="center">
                          <!--{$dropped.productsClass.name|h}-->
                          <!--{if $dropped.productsClass.classcategory_name1|@strlen > 0}-->
                            /<!--{$dropped.productsClass.classcategory_name1|h}-->
                          <!--{/if}-->
                          <!--{if $dropped.productsClass.classcategory_name2|@strlen > 0}-->
                            /<!--{$dropped.productsClass.classcategory_name2|h}-->
                          <!--{/if}-->
                        </td>
                        <td class="center"><!--{$dropped.price|number_format|h}-->円</td>
                        <td class="center"><!--{$dropped.quantity|h}--></td>
                    </tr>
                <!--{/foreach}-->
            </table>
            <!--{* カゴ落ち商品一覧表示テーブル *}-->
        <!--{else}-->
            <div class="message">カゴ落ち商品はありません。</div>
        <!--{/if}-->
</div>
