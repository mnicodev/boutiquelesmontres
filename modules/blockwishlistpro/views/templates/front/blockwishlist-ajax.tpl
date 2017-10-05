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

{if $products}
	<dl class="products" style="{if $products}border-bottom:1px solid #fff;{/if}">
	{foreach from=$products item=product name=i}
		{if $product.isobj eq 1}
			<dt class="{if $smarty.foreach.i.first}first_item{elseif $smarty.foreach.i.last}last_item{else}item{/if}">
				<span class="quantity-formated"><span class="quantity">{$product.quantity|intval}</span>x</span>
				<a class="cart_block_product_name" href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category_rewrite)|escape:'htmlall':'UTF-8'}" title="{$product.name|escape:'htmlall':'UTF-8'}" style="font-weight:bold;">{$product.name|truncate:13:'...'|escape:'htmlall':'UTF-8'}</a>
				<a class="ajax_cart_block_remove_link" href="javascript:;" onclick='javascript:WishlistCartpro("wishlist_block_list", "delete", "{$product.id_product|escape:'htmlall':'UTF-8'}", "{$product.id_product_attribute|escape:'htmlall':'UTF-8'}", "0", "{$static_token|escape:'htmlall':'UTF-8'}", "{$modulename|escape:'htmlall':'UTF-8'}","","","{l s='Impossible to delete because already offered purchase(s)' mod='blockwishlistpro'}","{l s='Product added to your list!' mod='blockwishlistpro'}");' title="{l s='remove this product from my wishlist' mod='blockwishlistpro'}"><img src="{$img_dir|escape:'htmlall':'UTF-8'}icon/delete.gif" alt="{l s='Delete' mod='blockwishlistpro'}" class="icon" /></a>
			</dt>
			{if $product.id_product_attribute AND isset($product.attributes_small)}	{* important to test product.id_product_attribute because of bug prestashop (1.4.4.0) which takes an attribute for a non attribute product*}
	{*		{if isset($product.attributes_small)}	 *}
			<dd class="{if $smarty.foreach.i.first}first_item{elseif $smarty.foreach.i.last}last_item{else}item{/if}" style="font-style:italic;margin:0 0 0 10px;">
				<a href="{$link->getProductLink($product.id_product, $product.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{l s='Product details' mod='blockwishlistpro'}">{$product.attributes_small|escape:'htmlall':'UTF-8'}</a>
			</dd>
			{/if}
		{/if}
	{/foreach}
	</dl>
{else}
	<dl class="products" style="font-size:10px;border-bottom:1px solid #fff;">
	{if isset($errors)}
		<dt>{l s='You must create a wishlist before adding products' mod='blockwishlistpro'}</dt>
	{else}
		<dt>{l s='No products' mod='blockwishlistpro'}</dt>
	{/if}
	</dl>
{/if}
