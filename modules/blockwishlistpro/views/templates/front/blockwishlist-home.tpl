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
<!-- blockwishlistpro homedisplay -->
<div id="wishlist_block_home" class="block account {if isset($hookTrigger) && $hookTrigger eq 'dispHome'}col-xs-{$blockWidth|escape:'htmlall':'UTF-8'}{/if}">
	<h3 class='page-heading'>
		<a href="{$wishlist_link|escape:'htmlall':'UTF-8'}">{l s='Wishlists' mod='blockwishlistpro'}</a>
	</h3>
	<div class="block_content">
		<div id="wishlist_block_home_search">
			<p class="center">
				<strong>{l s='Offer a gift' mod='blockwishlistpro'}</strong><br />
				{l s='Search for a list by name' mod='blockwishlistpro'}
			</p>
			<form method="post" action="{$search_link|escape:'htmlall':'UTF-8'}" name="searchform" >
				<p class="center">
					<img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/bullet.gif" alt="{l s='validate' mod='blockwishlistpro'}" height="7" width="10" />
					<input type="text" name="searchname" id="searchname" value="{l s='enter last name' mod='blockwishlistpro'}" onfocus="this.value='';this.style.cssText='color:black';" title="{l s='Please enter the last name of the creator of the list, or a part of it. For example smi for smith' mod='blockwishlistpro'}"/>
					<img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/helpd.png" alt="{l s='Please enter the last name of the creator of the list, or a part of it. For example smi for smith' mod='blockwishlistpro'}" title="{l s='Please enter the last name of the creator of the list, or a part of it. For example smi for smith' mod='blockwishlistpro'}" height="19" width="16" />
					<input type="hidden" name="pathurl" value="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}" />
					<input type="hidden" name="id_lang" value="{$id_lang|intval}" />
					<br />
				</p>
				<p class="center">
					{if $themeChoice eq 0}
					<input type="submit" name="searchsubmit" value="{l s='Search' mod='blockwishlistpro'}" class="exclusive"/>
					{else}
					<button type="submit" name="searchsubmit" class="btn btn-default button button-medium"><span>{l s='Search' mod='blockwishlistpro'}<i class="icon-search right"></i></span></button>
					{/if}
				</p>
			</form>
		</div>
		{if $wishlists && !isset($hookTrigger) || (isset($hookTrigger) && $hookTrigger neq 'dispHome')}
		<div id="wishlist_block_home_mng">
			<p class="center">
				<a href="{$wishlist_link|escape:'htmlall':'UTF-8'}" class="exclusive_large" title="{l s='Manage my list(s)' mod='blockwishlistpro'}"><span>{l s='Manage my list(s)' mod='blockwishlistpro'}</span></a>
			</p>
		</div>
		{/if}
{*		{if $wishlists}
*}
		{if $wishlists && !isset($hookTrigger) || (isset($hookTrigger) && $hookTrigger neq 'dispHome')}

		<div id="wishlist_block_home_view">
			<p>
				<strong>{l s='View my list(s)' mod='blockwishlistpro'}</strong><br />
				{l s='Select a list to display it below' mod='blockwishlistpro'}
				{l s='and view the left quantities' mod='blockwishlistpro'}
			</p>
			<p class="center">
				<img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/bullet.gif" alt="{l s='validate' mod='blockwishlistpro'}" height="7" width="10" />
				<select name="wishlists" id="wishlists" onchange="WishlistChangeDefaultpro('wishlist_block_list', $('#wishlists').val(), '{$modulename|escape:'htmlall':'UTF-8'}');">
				{foreach from=$wishlists item=wishlist name=i}
					<option value="{$wishlist.id_wishlist|escape:'htmlall':'UTF-8'}"{if $id_wishlist eq $wishlist.id_wishlist or ($id_wishlist == false and $smarty.foreach.i.first)} selected="selected"{/if}>{$wishlist.name|truncate:22:'...'|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
				</select>
			</p>
			<div id="wishlist_block_list" class="expanded">
				{if $wishlist_products}
					<dl class="products">
					{foreach from=$wishlist_products item=product name=i}
						{if $product.isobj eq 1}
							<dt class="{if $smarty.foreach.i.first}first_item{elseif $smarty.foreach.i.last}last_item{else}item{/if}">
								<span class="quantity-formated"><span class="quantity">{$product.quantity|intval}</span>x</span>
								<a class="cart_block_product_name"
								href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category_rewrite)|escape:'htmlall':'UTF-8'}" title="{$product.name|escape:'htmlall':'UTF-8'}">{$product.name|truncate:30:'...'|escape:'htmlall':'UTF-8'}</a>
								<a class="ajax_cart_block_remove_link" href="javascript:;" onclick='javascript:WishlistCartpro("wishlist_block_list", "delete", "{$product.id_product|escape:'htmlall':'UTF-8'}","{$product.id_product_attribute|escape:'htmlall':'UTF-8'}","0","{$ptoken|escape:'htmlall':'UTF-8'}", "{$modulename|escape:'htmlall':'UTF-8'}","","","{l s='Impossible to delete because already offered purchase(s)' mod='blockwishlistpro'}","{l s='Product added to your list!' mod='blockwishlistpro'}");' title="{l s='remove this product from my wishlist' mod='blockwishlistpro'}"><img src="{$img_dir|escape:'htmlall':'UTF-8'}icon/delete.gif" alt="{l s='Delete' mod='blockwishlistpro'}" class="icon" /></a>
							</dt>
							{if $product.id_product_attribute AND isset($product.attributes_small)}
							<dd class="{if $smarty.foreach.i.first}first_item{elseif $smarty.foreach.i.last}last_item{else}item{/if}">
								<a href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category_rewrite)|escape:'htmlall':'UTF-8'}" title="{l s='Product details' mod='blockwishlistpro'}">{$product.attributes_small|escape:'htmlall':'UTF-8'}</a>
							</dd>
							{/if}
						{/if}
					{/foreach}
					</dl>
				{else}
					<dl class="products">
						<dt>{l s='No products' mod='blockwishlistpro'}</dt>
					</dl>
				{/if}
			</div>
		</div>
		{/if}
	</div>
</div>