{**
* BLOCKWISHLISTPRO Front Office Feature - display products of a list, creator's view
*
* @author    Denis Deleval / alize-web.fr <contact@alizeweb.fr>
* @copyright Alizé Web 2011-2014
* @license   Non-exclusive license
 * The module is protected by copyright and other intellectual property laws
 * This license does not grant any reseller privileges
 * The module is identified as “Not-For-Resale” and may not be sold or otherwise transferred
 * You may not rent, redistribute, lease, lend, sell the module (even if you modify files), decompile, reverse engineer, or disassemble the module
 * Non exclusive license for one web site
 * Installation and use:  You may install, use, access, display and run one copy of the module for single website only
 * Multiple sites : buy as many licenses as the number of websites
 * You may modify files (.TPL, PHP, JS, CSS) provided with the module for the purpose of enhancing or customizing the product
 * but you cannot sell modified files
*}

{* translation parameters in onclick - take into account ' in translation fiels *}
<div style="display:none" id="add_cart_progress">{l s='Add to cart in progress, please wait a few seconds' mod='blockwishlistpro'}</div>
<div style="display:none" id="conf_cartreset">{l s='Products removed from cart. Donations are allowed from now onwards.' mod='blockwishlistpro'}</div>
{* end translation parameters *}

{* list/grid mode parameters *}
<input type="hidden" value="{$type_display_default|escape:'htmlall':'UTF-8'}" id="type_display_default" />
<input type="hidden" value="{$type_display_init|escape:'htmlall':'UTF-8'}" id="type_display_init" />

<input type="hidden" value="{$ajax|intval}" id="ajaxVal" />
<input type="hidden" value="{$sm_blockcart_is_enable|intval}" id="blockcartIsEnableVal" />
<input type="hidden" value="{$enable_on_current_device|intval}" id="enable_on_current_device" />

{if $isListConflict > 0}
{assign var=no_mixedcart1 value={l s='Your cart already contains products added from another list:' mod='blockwishlistpro'}}
{assign var=no_mixedcart2 value={l s='Hence you are not allowed to offer products from this list.' mod='blockwishlistpro'}}
{assign var=no_mixedcart2 value={l s='Hence you are not allowed to offer products from this list' mod='blockwishlistpro'}}
{assign var=conf_cartdelete value={l s='remove products from your cart and offer gifts from this list' mod='blockwishlistpro'}}
{assign var=conf_cartdelete1 value={l s='offer gifts related to this list' mod='blockwishlistpro'}}
{assign var=conf_cartdelete2 value={l s='your cart will be cleaned' mod='blockwishlistpro'}}
{assign var=confPrevList value={l s='give up this list and go to the list' mod='blockwishlistpro'}}
{assign var=go_msg_conflict value={l s='Go to the warning message on top of this page' mod='blockwishlistpro'}}
{assign var=fxit value={l s='Fix it!' mod='blockwishlistpro'}}
{/if}

<script type="text/javascript">
// <![CDATA[
// Images
var img_prod_dir = "{$img_prod_dir|escape:'htmlall':'UTF-8'}";
//Thickbox
ThickboxI18nImage = 'Image';
ThickboxI18nOf = '/';
ThickboxI18nClose = '{l s='X' mod='blockwishlistpro'}';
ThickboxI18nOrEscKey = '{l s='Esc' mod='blockwishlistpro'}';
ThickboxI18nNext = ">";
ThickboxI18nPrev = "<";
tb_pathToImage = '{$modules_dir|escape:"htmlall":"UTF-8"}{$moduleName|escape:"htmlall":"UTF-8"}/views/img/loadingAnimation.gif';
//]]>
</script>
{* breadcrumb - remove the following section if you don't want any breadcrumb to be displayed *}
{capture name=path}{*<span class="navigation-pipe"> {$navigationPipe|escape:'htmlall':'UTF-8'}</span>*}{l s='Wishlists' mod='blockwishlistpro'}{/capture}
{* breadcrumb - end of section *}

<div id="view_wishlist">
<h2>{l s='Wishlists' mod='blockwishlistpro'}</h2>
{if isset($wishlists) && $wishlists && $wishlists|@count >= 1}
{*		<p>
			{l s='Wishlists of' mod='blockwishlistpro'} {$current_wishlist.firstname} {$current_wishlist.lastname} <br />
			{foreach from=$wishlists item=wishlist name=i}
			{assign var="next" value="`$smarty.foreach.i.index+1`"}
				{if $wishlist.published == 1}
					<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/{$moduleName|escape:'htmlall':'UTF-8'}/view.php?token={$wishlist.token|escape:'htmlall':'UTF-8'}">{$wishlist.name|escape:'htmlall':'UTF-8'}</a>

						{if !$smarty.foreach.i.first AND !$smarty.foreach.i.last}
							/
						{/if}
				{/if}
			{/foreach}
		</p>
*}
		<br />
		{if $displa neq ''}
		<div class='alert alert-success mg_bottom12' id='cleancartOk'>
			{$displa|escape:'htmlall':'UTF-8'}
		</div>
		{/if}

		{if isset($products) && $products}
			{*define number of products per line in other page for desktop*}
				{assign var='nbItemsPerLine' value=3}
				{assign var='nbItemsPerLineTablet' value=2}
				{assign var='nbItemsPerLineMobile' value=3}
			{*define numbers of product per line in other page for tablet*}
			{assign var='nbLi' value=$products|@count}
			{math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
			{math equation="nbLi/nbItemsPerLineTablet" nbLi=$nbLi nbItemsPerLineTablet=$nbItemsPerLineTablet assign=nbLinesTablet}
		<div class="addresses" id="view-producs_block_center">
			<h3 class="addresses_dd">{l s='Welcome to the wishlist of' mod='blockwishlistpro'} {$current_wishlist.firstname|escape:'htmlall':'UTF-8'} {$current_wishlist.lastname|escape:'htmlall':'UTF-8'} : {$current_wishlist.name|escape:'htmlall':'UTF-8'}</h3>
			<br />
			{if $current_wishlist.published == 0}
				{l s='Sorry, the wishlist' mod='blockwishlistpro'} <strong style="color:#EE0000">{$current_wishlist.name|escape:'htmlall':'UTF-8'}</strong> {l s='exists but' mod='blockwishlistpro'} <strong>{l s='has not been published yet' mod='blockwishlistpro'}</strong>.
				<br />
				<br />
				{l s='Please contact the author of the list to let her/him know' mod='blockwishlistpro'}.
				<br />
			{else}
				{if $isListConflict > 0}
				<div class='alert alert-warning{if $themeChoice eq 0} bkg_orange pdg4 brad3{/if}'>
					<span>
						{$no_mixedcart1|escape:'htmlall':'UTF-8'}
						<a href="{$link->getModuleLink('blockwishlistpro', 'view', ['token'=>{$tabListConflict.0.token|escape:'htmlall':'UTF-8'}|escape:'htmlall':'UTF-8'])|escape:'htmlall':'UTF-8'}" title="{l s='Go to the list web page like a donator' mod='blockwishlistpro'}">{$tabListConflict.0.name|escape:'htmlall':'UTF-8'}</a>.
					</span>
					<span> {$no_mixedcart2|escape:'htmlall':'UTF-8'} ({$current_wishlist.name|escape:'htmlall':'UTF-8'}).</span>
					<div class="clearfix mg_top12">
						<label>{l s='Your choice:' mod='blockwishlistpro'}</label>
						<br />
						<label>{l s='either' mod='blockwishlistpro'}</label>
						<form action="{$link->getModuleLink('blockwishlistpro', 'view', ['token'=>{$token|escape:'htmlall':'UTF-8'}|escape:'htmlall':'UTF-8'])|escape:'htmlall':'UTF-8'}" method="post" class="std">
						<fieldset>
							<div class="form-group">
								<button class="btn btn-default button button-small" name="mx_remove_cart" type="submit">
									<span>{$conf_cartdelete1|escape:'htmlall':'UTF-8'} {$current_wishlist.name|escape:'htmlall':'UTF-8'}<i class="icon-chevron-right right"></i></span>
								</button> ({$conf_cartdelete2|escape:'htmlall':'UTF-8'})
							</div>
						</fieldset></form>
						<label>{l s='or' mod='blockwishlistpro'}</label>
						<div class="lnk">
							<a class="btn btn-default button" href="{$link->getModuleLink('blockwishlistpro', 'view', ['token'=>{$tabListConflict.0.token|escape:'htmlall':'UTF-8'}|escape:'htmlall':'UTF-8'])|escape:'htmlall':'UTF-8'}">
								<span>{$confPrevList|escape:'htmlall':'UTF-8'} "{$tabListConflict.0.name|escape:'htmlall':'UTF-8'}"<i class="icon-chevron-right right"></i></span>
							</a>
						</div>

					</div>
				</div>
				<br /><br /><br />
				{/if}
			{/if}

			{if $current_wishlist.published == 1}
				{if $themeChoice eq 1}
					<div class="content_sortPagiBar clearfix">
						<div class="clearfix">
							<ul class="display hidden-xs">
								<li class="display-title">{l s='View:' mod='blockwishlistpro'}</li>
							    <li id="grid_aw"><a rel="nofollow" href="#" title="{l s='Grid' mod='blockwishlistpro'}"><i class="icon-th-large"></i>{l s='Grid' mod='blockwishlistpro'}</a></li>
							    <li id="list_aw"><a rel="nofollow" href="#" title="{l s='List' mod='blockwishlistpro'}"><i class="icon-th-list"></i>{l s='List' mod='blockwishlistpro'}</a></li>
							</ul>
						</div>
					</div>
				{/if}

				<ul class="product_list_aw grid{if $themeChoice eq 1} row  modern1{else} theme_classic{/if}">
					{foreach from=$products item=product name=i}

						{math equation="(total%perLine)" total=$smarty.foreach.i.total perLine=$nbItemsPerLine assign=totModulo}
						{math equation="(total%perLineT)" total=$smarty.foreach.i.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
						{math equation="(total%perLineT)" total=$smarty.foreach.i.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
						{if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
						{if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
						{if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}

{*						{if $product.isobj eq 1 && (($product.isobj_attr eq 1 && $product.is_attr_exist eq 1) || ($product.isobj_attr eq 0 && $product.id_product_attribute eq 0))}
*}
{if 1 eq 1}
 {*product or attribute exist in database*}
					<li class="mg_top12 mg_bottom24 address_dd  {if $smarty.foreach.i.last}last_item{elseif $smarty.foreach.i.first}first_item{/if} {if $themeChoice eq 0 &&  $smarty.foreach.i.index % 2}alternate_item{else}item{/if} {if $themeChoice eq 1}col-xs-12 col-sm-6 col-md-4{if $smarty.foreach.i.iteration%$nbItemsPerLine == 0} last-in-line{elseif $smarty.foreach.i.iteration%$nbItemsPerLine == 1} first-in-line{/if}{if $smarty.foreach.i.iteration > ($smarty.foreach.i.total - $totModulo)} last-line{/if}{if $smarty.foreach.i.iteration%$nbItemsPerLineTablet == 0} last-item-of-tablet-line{elseif $smarty.foreach.i.iteration%$nbItemsPerLineTablet == 1} first-item-of-tablet-line{/if}{if $smarty.foreach.i.iteration%$nbItemsPerLineMobile == 0} last-item-of-mobile-line{elseif $smarty.foreach.i.iteration%$nbItemsPerLineMobile == 1} first-item-of-mobile-line{/if}{if $smarty.foreach.i.iteration > ($smarty.foreach.i.total - $totModuloMobile)} last-mobile-line{/if}{else}theme_classic{/if}" id="block_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}">
						<div class="address_titled border_top1">&nbsp;{$product.name|truncate:60:'...'|escape:'htmlall':'UTF-8'}</div>
						<div class="blk_left{*{if $themeChoice eq 0} width35p{/if}*}">
							<div class="address_name">
								{if $product.have_image}
								<a class="thickbox product_image" href="{$link->getImageLink($product.link_rewrite, $product.cover, 'large_default')|escape:'htmlall':'UTF-8'}" title="{l s='Product\'s image' mod='blockwishlistpro'}">
									<img src="{$link->getImageLink($product.link_rewrite, $product.cover, $type_medium)|escape:'htmlall':'UTF-8'}" title="{$product.name|escape:'htmlall':'UTF-8'}" alt="{$product.name|escape:'htmlall':'UTF-8'}" width="{$mediumSize.width|escape:'htmlall':'UTF-8'}" height="{$mediumSize.height|escape:'htmlall':'UTF-8'}" class="replace-2x img-responsive" />
								</a>
								{else}
								<a class="thickbox product_image" href="{$img_prod_dir|escape:'htmlall':'UTF-8'}{$lang_iso|escape:'htmlall':'UTF-8'}-default-large_default.jpg" title="{l s='Product\'s image' mod='blockwishlistpro'}">
									<img src="{$img_prod_dir|escape:'htmlall':'UTF-8'}{$lang_iso|escape:'htmlall':'UTF-8'}-default-large_default.jpg" alt="{$product.name|escape:'htmlall':'UTF-8'}" title="{$product.name|escape:'htmlall':'UTF-8'}" width="{$mediumSize.width|escape:'htmlall':'UTF-8'}" height="{$mediumSize.height|escape:'htmlall':'UTF-8'}" class="replace-2x img-responsive" />
								</a>
								{/if}
							</div>
							<div class="view_align_dd">
								<div class="description_short_dd">
								{if $product.description_short!==""}
									<p>{$product.description_short|escape:'htmlall':'UTF-8'|truncate:36:'...'}</p>
								{/if}
								</div>
								<div class='mg_bottom6 height1_4em clearboth'>
								{if $product.id_product_attribute AND isset($product.attributes_small)}	{* important to test product.id_product_attribute because of bug prestashop (1.4.4.0) which takes an attribute for a non attribute product *}
									<p>{$product.attributes_small|escape:'htmlall':'UTF-8'}</p>
								{/if}
								</div>
								<div class="description_dd height1_4em ovflow_none">
								{if $product.description !== ""}
									<a href= "{$modules_dir|escape:'htmlall':'UTF-8'}{$moduleName|escape:'htmlall':'UTF-8'}/texte-popup.php?idText={$product.id_product|intval}&amp;KeepThis=true&amp;TB_iframe=true&amp;height=350&amp;width=400" class="thickbox info" title="{l s='More details' mod='blockwishlistpro'}">{$product.description|strip_tags:'UTF-8'|truncate:66:'...'}</a>
									<a href= "{$modules_dir|escape:'htmlall':'UTF-8'}{$moduleName|escape:'htmlall':'UTF-8'}/texte-popup.php?idText={$product.id_product|intval}&amp;KeepThis=true&amp;TB_iframe=true&amp;height=350&amp;width=400" class="thickbox info" title="{l s='More details' mod='blockwishlistpro'}">&nbsp;&nbsp;<img src="{$modules_dir|escape:'htmlall':'UTF-8'}{$moduleName|escape:'htmlall':'UTF-8'}/views/img/bullet.gif" alt="{l s='More details' mod='blockwishlistpro'}" /></a>
								{/if}
								</div>
								<hr class="separationd" />
							</div>
						</div>
						<div class="wishlist_product_detail_dd pg_right6{if $themeChoice eq 1} border_right1 mg_top6{/if}">
							<span class="price"> {convertPrice price=$product.price_dd}</span>
							<hr style="clear:both; visibility:hidden; margin:0" />
							{if (isset($product.attribute_quantity) AND $product.attribute_quantity >= 1 OR !isset($product.attribute_quantity) AND $product.product_quantity >= 1) OR !$stock_management OR ($stock_management AND $product.product_out_of_stock|intval eq 2 AND $order_out_of_stock) OR ($stock_management AND $product.product_out_of_stock|intval eq 1)} <!-- check stock availability or stock management desactivate or allow order even if out of stock -->

			{*						{if ((isset($product.attribute_quantity) AND $product.attribute_quantity >= 1) OR (!isset($product.attribute_quantity) AND $product.product_quantity >= 1)) OR !$stock_management OR ($stock_management AND $order_out_of_stock)} <!-- check stock availability or stock management desactivate or allow order even if out of stock -->*}
								{if !$ajax OR ($ajax AND ($sm_blockcart_is_enable==0 OR !$enable_on_current_device))}
								<form id="addtocart_{$product.id_product|intval}_{$product.id_product_attribute|intval}" action="{$base_dir_ssl|escape:'htmlall':'UTF-8'}cart.php" method="post">
								<p class="hidden">
									<input type="hidden" name="id_product" value="{$product.id_product|intval}" id="product_page_product_id"  />
									<!--<input type="hidden" name="add" value="1" /> -->
									<input name="add" value="1" />
									<input type="hidden" name="qty" value="" />
									<input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}" />
									<input type="hidden" name="id_product_attribute" id="idCombination" value="{$product.id_product_attribute|intval}" />
								</p>
								</form>
								{/if}

								{if !$PS_CATALOG_MODE && $isListConflict eq 0}
								<form class="form_view_qty_offer" action=""><p>{l s='Quantity to offer' mod='blockwishlistpro'} : <input type="text" id="enter_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}" size="3" value="1" style="margin-left:0;border: 1px solid #BDC2C9;" title="{l s='Enter the quantity you want to offer' mod='blockwishlistpro'}" /></p></form>
								<hr style="clear:both; visibility:hidden; margin:0" />
								<a href="javascript:;" class="{if $themeChoice eq 0}exclusive_large{else}btn btn-default button button-medium{/if}" onclick='WishlistBuyProductpro("{$token|escape:'htmlall':'UTF-8'}", "{$product.id_product|escape:'htmlall':'UTF-8'}", "{$product.id_product_attribute|escape:'htmlall':'UTF-8'}","{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}", this, {$ajax|escape:'htmlall':'UTF-8'}, $("#enter_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}").val(),{$sm_blockcart_is_enable|escape:'htmlall':'UTF-8'},$("#add_cart_progress").html(), "{$link->getPageLink('cart')|escape:'htmlall':'UTF-8'}", "{$enable_on_current_device|escape:'htmlall':'UTF-8'}");' title="{l s='Offer the gift' mod='blockwishlistpro'}" id="a_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}"><span style='margin:0'>{l s='Offer the gift' mod='blockwishlistpro'}</span></a>
								{elseif !$PS_CATALOG_MODE && $isListConflict eq 1}
								<p class="no_stock width220px fr_aw">{$no_mixedcart1|escape:'htmlall':'UTF-8'} {$tabListConflict.0.name|escape:'htmlall':'UTF-8'}</p>
								<a class="pdg4 fr_aw clearboth border1_red center brad3" onclick="scrollToAw('view-producs_block_center', 700);" title='{$go_msg_conflict|escape:'htmlall':'UTF-8'}'>{$fxit|escape:'htmlall':'UTF-8'}</a>
								{/if}
							{else}
								{if !$PS_CATALOG_MODE}
								<hr style="clear:both; visibility:hidden; margin:0" />
								<form class="form_view_qty_offer" action=""><p>{l s='Quantity to offer' mod='blockwishlistpro'} : <input type="text" id="enter_{$product.id_product|intval}_{$product.id_product_attribute|intval}" size="3" disabled="disabled" value="0" style="margin-left:0;border: 1px solid #BDC2C9; background-color:#AAAAAA" /></p>
								<p class="no_stock">{l s='This product is no longer in stock' mod='blockwishlistpro'}</p>
								</form>
								<hr style="clear:both; visibility:hidden" />
								<span class="exclusive not_offer" title="{l s='This product is no longer in stock' mod='blockwishlistpro'}">{l s='Offer the gift' mod='blockwishlistpro'}</span>
								{/if}
							{/if}
							<hr class="separationd" />
							{if $isListConflict eq 0}
							<form class="form_view_qty_left mg_top24" name="formleft" action="">
								<p>{l s='quantity remaining on the list' mod='blockwishlistpro'} :
								<input type="text" id="nnn_{$product.id_product|intval}_{$product.id_product_attribute|intval}" name="inpleft" size="3" value="{$product.quantity|intval}" disabled="disabled" style="margin-left:0" /></p></form>
							<hr class="separationd" />
							{/if}
						</div>
						<hr class="separationd" />
						{/if} {* end if $product.isobj ...*}
					{/foreach}
					</li>
				</ul>
			<p class="clear" />
			{/if} <!-- published -->
		</div> {*div addresses*}
		{else} {*isset($products)...*}
			<br /><br />
			{if $current_wishlist.published == 0}
				{l s='Sorry, the wishlist' mod='blockwishlistpro'} <strong style="color:#EE0000">{$current_wishlist.name|escape:'htmlall':'UTF-8'}</strong> {l s='exists but contains no products and' mod='blockwishlistpro'} <strong>{l s='has not been published yet' mod='blockwishlistpro'}</strong>.
				<br />
				{l s='Please contact the author of the list to let her/him know' mod='blockwishlistpro'}.
				<br />
			{else}
				&nbsp;&nbsp;{l s='Sorry, the wishlist' mod='blockwishlistpro'} <strong style="color:#EE0000">{$current_wishlist.name|escape:'htmlall':'UTF-8'}</strong> <strong>{l s='contains no products' mod='blockwishlistpro'}</strong>.

			{/if}
		{/if} <!-- products -->
{/if} {* end isset(wishlists) ... *}
</div>
{if isset($errors_wl) && $errors_wl && $errors_wl|@count >= 1}
	{include file="$tpl_dir./errors.tpl"}
	<div class="error">
		<p>{if $errors_wl|@count > 1}{l s='There are %d errors' sprintf=$errors_wl|@count mod='blockwishlistpro'}{else}{l s='There is %d error' sprintf=$errors_wl|@count mod='blockwishlistpro'}{/if}</p>
		<ol>
		{foreach from=$errors_wl key=k item=error}
			<li>{$error|escape:'htmlall':'UTF-8'}</li>
		{/foreach}
		</ol>
		{if isset($smarty.server.HTTP_REFERER) && !strstr($request_uri, 'authentication') && preg_replace('#^https?://[^/]+/#', '/', $smarty.server.HTTP_REFERER) != $request_uri}
			<p class="lnk"><a href="{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}" title="{l s='Back' mod='blockwishlistpro'}">&laquo; {l s='Back' mod='blockwishlistpro'}</a></p>
		{/if}
	</div>
{/if}
