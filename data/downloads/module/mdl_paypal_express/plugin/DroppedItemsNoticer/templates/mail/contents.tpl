<!--{include file=$smarty.const.DROPPED_ITEMS_NOTICER_HEADER}-->
<div>
  <p style="font-weight: bold; font-size: 14px;">最近カゴに入れた商品</p>
<div>
  <!--{foreach from=$arrCart item=item}-->
  <div class="items" style="margin-top: 45px; margin-bottom: 45px">
    <div><img src="<!--{$smarty.const.HTTP_URL}-->upload/save_image/<!--{$item.productsClass.main_list_image|h}-->" /></div>
    <div style="font-size: 12px; margin-top: 5px;">商品コード: <!--{$item.productsClass.product_code|h}--></div>
    <div style="font-size: 19px; height: 28px;">
      <!--{$item.productsClass.name|h}-->
      <!--{if $item.productsClass.classcategory_name1|@strlen > 0}-->
        /<!--{$item.productsClass.classcategory_name1|h}-->
      <!--{/if}-->
      <!--{if $item.productsClass.classcategory_name2|@strlen > 0}-->
        /<!--{$item.productsClass.classcategory_name2|h}-->
      <!--{/if}-->
    </div>
    <div style="font-size: 12px; color: #F00;"><!--{$smarty.const.SALE_PRICE_TITLE}-->(税込): <!--{$item.price|number_format}--> 円</div>
    <!--{if $smarty.const.USE_POINT == true}--><div style="font-size: 12px; color: #F00; font-weight: bold">ポイント: <!--{$item.productsClass.price02|sfPrePoint:$item.productsClass.point_rate|number_format}--> pt</div><!--{/if}-->
    <div style="margin-top: 5px; margin-bottom: 5px;"><!--{$item.main_list_comment}--></div>
    <div style="background-color: #ecf5ff; border: 1px solid #cef0f4; padding: 20px 10px; width: 300px; margin-bottom: 30px;">
      <div style="font-size: 12px">数量: <!--{$item.quantity|h}--></div>
      <a href="<!--{$smarty.const.HTTP_URL}-->products/list.php?product_id=<!--{$item.productsClass.product_id|h}-->&product_class_id=<!--{$item.productsClass.product_class_id|h}-->&quantity=<!--{$item.quantity|h}-->&authcode=<!--{$item.authcode|h}-->"><img src="<!--{$smarty.const.HTTP_URL}-->user_data/packages/default/img/button/btn_cartin.jpg" alt="カゴに入れる" style="display: block; width: 160px; margin-left: auto; margin-right: auto;" /></a></div>
  </div>
  <!--{/foreach}-->
</div>
<!--{include file=$smarty.const.DROPPED_ITEMS_NOTICER_FOOTER}-->

