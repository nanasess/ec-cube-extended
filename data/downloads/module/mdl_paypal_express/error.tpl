<!--{*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2011 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *}-->
<script type="text/javascript">//<![CDATA[
//]]></script>

<!--CONTENTS-->
<div id="undercolumn">
    <div id="undercolumn_shopping">
        <p class="flow_area"><img src="<!--{$TPL_URLPATH}-->img/picture/img_flow_03.jpg" alt="購入手続きの流れ" /></p>
        <h2 class="title"><!--{$tpl_title|h}--></h2>

        <p class="attention">購入中にエラーが発生しました:<br /><!--{$tpl_message}--></p>

        <div class="btn_area">
            <ul>
                <li>
                    <a href="<!--{$smarty.const.CART_URLPATH}-->" onmouseover="chgImg('<!--{$TPL_URLPATH}-->img/button/btn_back_on.jpg','back<!--{$key}-->');" onmouseout="chgImg('<!--{$TPL_URLPATH}-->img/button/btn_back.jpg','back<!--{$key}-->');"><img src="<!--{$TPL_URLPATH}-->img/button/btn_back.jpg" alt="戻る" name="back<!--{$key}-->" /></a>
                </li>
              </ul>
        </div>
    </div>
</div>
<!--▲CONTENTS-->
