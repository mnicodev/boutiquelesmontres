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

{* to be displayed on product page *}
{if isset($wishlists) && $wishlists && $wishlists|count > 1}
<br />
<div id="pdt_add_list_wl">
	<div id="wl_pdt_page">
		<p>
			{l s='Select a list to add the product to it' mod='blockwishlistpro'}
		</p>
		<select name="wishlists_pdt" id="wishlists_pdt" onchange="WishlistChangeDefaultpro('wishlist_block_list', $('#wishlists_pdt').val(), '{$modulename|escape:'htmlall':'UTF-8'}');">
			{foreach from=$wishlists item=wishlist name=i}
				<option value="{$wishlist.id_wishlist|escape:'htmlall':'UTF-8'}"{if $id_wishlist eq $wishlist.id_wishlist or ($id_wishlist == false and $smarty.foreach.i.first)} selected="selected"{/if}>{$wishlist.name|truncate:22:'...'|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
		</select>
	</div>
{/if}
	<p class="buttons_bottom_block" id="">
		<a href="javascript:;" class="icon-wishlist" onclick='WishlistCartpro("wishlist_block_list", "add", "{$id_product|intval}", $("#idCombination").val(), $("#quantity_wanted").val(), "", "{$modulename|escape:'htmlall':'UTF-8'}","{l s='You need to be logged to add products to your wishlist' mod='blockwishlistpro'}","{l s='Please create your list before adding products (my account/my lists)' mod='blockwishlistpro'}","","{l s='Product added to your list!' mod='blockwishlistpro'}");'>
		ajout
		</a>
	</p>
{if isset($wishlists) && $wishlists && $wishlists|count > 1}
</div>
{/if}
