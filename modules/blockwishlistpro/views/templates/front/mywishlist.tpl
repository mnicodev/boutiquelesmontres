{**
* BLOCKGIFTLISTPRO Front Office Feature - display products of a list, creator's view
*
* @author    Denis Deleval / alize-web.fr <contact@alizeweb.fr>
* @copyright Alizé Web
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
<script type="text/javascript">
 // <![CDATA[
	var displayList = '{$displayList|escape:'htmlall':'UTF-8'}';
//]]>
</script>
{* not compatible in PS1.5
{strip}
{addJsDef displayList=Configuration::get('PS_GRID_PRODUCT')|boolval}
{/strip}
*}
{* list/grid mode parameters *}
<input type="hidden" value="{$type_display_default|escape:'htmlall':'UTF-8'}" id="type_display_default" />
<input type="hidden" value="{$type_display_init|escape:'htmlall':'UTF-8'}" id="type_display_init" />

<hr class="separationd" />
<div id="mywishlist_pro">
	{capture name=path}<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">{l s='My account' mod='blockwishlistpro'}</a><span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>{l s='My wishlists' mod='blockwishlistpro'}{/capture}
	{if $spy_breadcrumb eq 0}
		{include file="$tpl_dir./breadcrumb.tpl"}
	{/if}

	<h2>{l s='My wishlists' mod='blockwishlistpro'}</h2>
	{if isset($errors) && $errors && $errors|@count > 1}
		{include file="$tpl_dir./errors.tpl"}
	{elseif isset($err_dd) && $err_dd && $err_dd|@count >= 1}
		<div class="error alert alert-warning{if $themeChoice eq 0} bkg_orange pdg4 brad3{/if}">
			<p>{if $err_dd|@count > 1}{l s='There are %d errors' sprintf=$err_dd|@count mod='blockwishlistpro'}{else}{l s='There is %d error' sprintf=$err_dd|@count mod='blockwishlistpro'}{/if}</p>
			<ol>
			{foreach from=$err_dd key=k item=error}
				<li>{$error|escape:'htmlall':'UTF-8'}</li>
			{/foreach}
			</ol>
			{*
			{if isset($smarty.server.HTTP_REFERER) && !strstr($request_uri, 'authentication') && preg_replace('#^https?://[^/]+/#', '/', $smarty.server.HTTP_REFERER) != $request_uri}
				<p class="lnk"><a href="{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}" title="{l s='Back' mod='blockwishlistpro'}">&laquo; {l s='Back' mod='blockwishlistpro'}</a></p>
			{/if}
			*}
		</div>
	{/if}
	{if $id_customer|intval neq 0}
{* translation parameters in onclick - take into account ' in translation fiels *}
<div style="display:none" id="publish">{l s='Do you really want to publish the list ?' mod='blockwishlistpro'}</div>
<div style="display:none" id="unpub">{l s='Do you really want to un-publish the list ?' mod='blockwishlistpro'}</div>
<div style="display:none" id="clikpub">{l s='click to publish the list' mod='blockwishlistpro'}</div>
<div style="display:none" id="clikunpub">{l s='click to unpublish the list' mod='blockwishlistpro'}</div>
<div style="display:none" id="delete_confirm">{l s='Do you really want to delete this wishlist ?' mod='blockwishlistpro'}</div>
<div style="display:none" id="delete_impossible">{l s='Already bought products, impossible to delete the list ! Tip : un-publish the list to prevent it from being displayed.' mod='blockwishlistpro'}</div>
{* end translation parameters *}
		<form id="creator_choose_name" method="post" class="std" >
			<fieldset>
				<a id="shownewwl" onclick="newWlVisibilitypro('block_newwl', 'newwl');return false;">&nbsp;{l s='New wishlist - Creation and directions for use' mod='blockwishlistpro'}&nbsp;&nbsp;
					{if $themeChoice eq 0}
					<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/more.gif" width="10" height="10" />
					{else}
					<i class="icon-plus-square right"></i>
					{/if}
				</a>
				<a id="hidenewwl" style="display:none" onclick="newWlVisibilitypro('block_newwl', 'newwl');">&nbsp;{l s='New wishlist - Creation and directions for use' mod='blockwishlistpro'}&nbsp;&nbsp;
					{if $themeChoice eq 0}
					<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/less.gif" width="10" height="10" />
					{else}
					<i class="icon-minus-square right"></i>
					{/if}
				</a>

				<input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}" />

<!--				<div class="block_newwl" style="display:none">  -->
				<div class="block_newwl" id="block_newwl_dd">
					<noscript><br />{l s='You should enable javascript to manage your list(s)' mod='blockwishlistpro'}<br /></noscript>
					<br />
					<div id="info_complete_guide">
						<a href="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/creatorguide-{$lang_iso|escape:'htmlall':'UTF-8'}.pdf" target="_blank"><img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/help2.png" /><br />{l s='Complete guide' mod='blockwishlistpro'}</a>
					</div>
					<div id="info_process_wldd">
						<h4>1. {l s='Choose a name' mod='blockwishlistpro'}</h4>
						<p>{l s='Enter the' mod='blockwishlistpro'} <strong>{l s='name' mod='blockwishlistpro'}</strong> {l s='of the new wishlist' mod='blockwishlistpro'}</p>
						<input type="text" id="name" name="name" value="" />
						{if $themeChoice eq 0}
						<input type="submit" name="submitWishlist" id="submitWishlist" value="{l s='Save' mod='blockwishlistpro'}" class="exclusive" title="{l s='Save your new list' mod='blockwishlistpro'}" />
						{else}
						<button type="submit" name="submitWishlist" id="submitWishlist" class="btn btn-default button button-small"><span>{l s='Save' mod='blockwishlistpro'}<i class="icon-chevron-right right"></i></span></button>
						{/if}
					</div>
					<hr class="separationd" />
					<br /><br />
					<h4>2. {l s='Add products' mod='blockwishlistpro'}</h4>
					<p><em>{l s='Should you have more than one list, please select the list in the \'Wishlists\' block before adding products' mod='blockwishlistpro'}</em>.</p>
					<p>{l s='Go to each' mod='blockwishlistpro'} <strong>{l s='product page' mod='blockwishlistpro'}</strong> {l s='you want to add to your list. Click' mod='blockwishlistpro'} '<strong>{l s='Add to list' mod='blockwishlistpro'}</strong>' {l s='and adjust quantity if needs be' mod='blockwishlistpro'}.</p>
					<p><em> {l s='Should you want to change quantity later on, please click the name of the list on this page (in  \'My current wishlists\' section below) and then \'Products management\'' mod='blockwishlistpro'}</em>.</p>

					<br />
					<h4>3. {l s='Publish the list' mod='blockwishlistpro'}</h4>
					<p>{l s='Once you have selected your products (step 2), click' mod='blockwishlistpro'} '<strong>{l s='Publish' mod='blockwishlistpro'}</strong>' {l s='below on this page to display your list in search list results and if you want to allow purchases' mod='blockwishlistpro'}.</p>
					<p><em>{l s='FYI, you can return here by clicking either on \'My wishlists\' in \'My account\' block or on the \'Manage my list(s)\' button visible on each page in the wishlist block.' mod='blockwishlistpro'}</em></p>

				</div>
				<script type="text/javascript">
				// <![CDATA[
					// we hide the block only if JavaScript is activated
					document.getElementById('block_newwl_dd').style.cssText="display:none";
				// ]]>
				</script>
				{*DD bug IE9, bloc ne se déplie pas *}
				<div id="empty_dd"></div>
			</fieldset>
		</form>

		{if $wishlists}
		<br />
		<div id="block-history" class="block-center">

		<h3>&nbsp;{l s='My current wishlists' mod='blockwishlistpro'} </h3>

		<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/arrow_down.png" width="16" height="16" alt="{l s='My current wishlists' mod='blockwishlistpro'}" id="arrowdown" /><br />
		<noscript><br />{l s='You should enable javascript to manage your list(s)' mod='blockwishlistpro'}<br /></noscript>
			<table class="std">
				<thead>
					<tr>
						<th class="first_item" title="{l s='Manage the wishlist (products management, list of Gifts, email sending)' mod='blockwishlistpro'}">{l s='Manage' mod='blockwishlistpro'} <!--{l s='Name' mod='blockwishlistpro'}--></th>
						<th class="item mywishlist_second" title="{l s='Publish the list : if checked it will be available for buying products and displayed in search list results.' mod='blockwishlistpro'}">{l s='Publish' mod='blockwishlistpro'}</th>
						<th class="item mywishlist_first hidden-xs" title="{l s='View the summary wishlist PDF : offered products and donators, messages of donators' mod='blockwishlistpro'}">{l s='PDF' mod='blockwishlistpro'}</th>
						<th class="item mywishlist_first hidden-xs" title="{l s='Left quantity' mod='blockwishlistpro'}">{l s='Qty' mod='blockwishlistpro'}</th>
						<th class="item mywishlist_first" title="{l s='How many times the list has been displayed ' mod='blockwishlistpro'}">{l s='Viewed' mod='blockwishlistpro'}</th>
						<th class="item mywishlist_second" title="{l s='Created on ... ' mod='blockwishlistpro'}">{l s='Created' mod='blockwishlistpro'}</th>
						<th class="item mywishlist_second" title="{l s='Go to the list web page like a donator' mod='blockwishlistpro'}">{l s='Direct Link' mod='blockwishlistpro'}</th>
						<th class="last_item mywishlist_first" title="{l s='Delete this list' mod='blockwishlistpro'}">{l s='Delete' mod='blockwishlistpro'}</th>
					</tr>
				</thead>

				<tbody>
				{section name=i loop=$wishlists}
					<tr id="wishlist_{$wishlists[i].id_wishlist|intval}">
						<td class="bold" style="width:200px;">
							<a href="javascript:;" title="{l s='Manage the wishlist (products management, offered products, email sending)' mod='blockwishlistpro'}" onclick='javascript:WishlistManagePro("block-order-detail", "{$wishlists[i].id_wishlist|intval}");' class="underline bold">{$wishlists[i].name|truncate:30:'...'|escape:'htmlall':'UTF-8'}</a></td>

						<td class="center"  title="{l s='Publish the list : it will be available for buying products and displayed in search list results.' mod='blockwishlistpro'}">

							{if  $wishlists[i].published|intval == 1}
							<input type="checkbox" id="checkbox_{$wishlists[i].id_wishlist|intval}" value="1" checked="checked" onClick='return (WishlistPublish($("#checkbox_{$wishlists[i].id_wishlist|intval}").val(), "{$content_dir|escape:'htmlall':'UTF-8'}modules/", "{$modulename|escape:'htmlall':'UTF-8'}", "checkbox_{$wishlists[i].id_wishlist|intval}", "{$wishlists[i].id_wishlist|intval}", $("#publish").html(), $("#unpub").html(), $("#clikpub").html(), $("#clikunpub").html()));' title="{l s='click to unpublish the wishlist' mod='blockwishlistpro'}"/>
							{else}
							<input type="checkbox" id="checkbox_{$wishlists[i].id_wishlist|intval}" value="0" onClick='return (WishlistPublish($("#checkbox_{$wishlists[i].id_wishlist|intval}").val(), "{$content_dir|escape:'htmlall':'UTF-8'}modules/", "{$modulename|escape:'htmlall':'UTF-8'}", "checkbox_{$wishlists[i].id_wishlist|intval}", "{$wishlists[i].id_wishlist|intval}", $("#publish").html(), $("#unpub").html(), $("#clikpub").html(), $("#clikunpub").html()));' title="{l s='click to publish the wishlist' mod='blockwishlistpro'}"/>
							{/if}
						</td>
						<td class="bold center" style="width:20px;">
							{if $wishlists[i].is_gift}
								<a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/pdf_wl_tcpdf.php?id_wishlist={$wishlists[i].id_wishlist|intval}&id_lang={$id_lang|escape:'htmlall':'UTF-8'}" title="{l s='View the summary wishlist PDF : offered products and donators, messages of donators' mod='blockwishlistpro'}"><img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/pdf.gif"/></a>
							{else}
								{assign var=al_ch value={l s='Sorry, no pdf available because no gifts have been already offered' mod='blockwishlistpro'}|escape:'quotes'}
								<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/pdf-no.gif" alt="{l s='Sorry, no pdf available because no gifts have been already offered' mod='blockwishlistpro'}" title="{l s='Sorry, no pdf available because no gifts have been already offered' mod='blockwishlistpro'}" alt="{l s='Sorry, no pdf available because no gifts have been already offered' mod='blockwishlistpro'}" onclick="javascript:alert('{$al_ch|escape:'htmlall':'UTF-8'}');" />
							{/if}
						</td>

						<td class="bold center hidden-xs" title="{l s='Left quantity' mod='blockwishlistpro'}">
							{assign var=n value=0}
							{foreach from=$nbProducts item=nb name=i}
								{if $nb.id_wishlist eq $wishlists[i].id_wishlist}
									{assign var=n value=$nb.nbProducts|intval}
								{/if}
							{/foreach}

							{if $n}
								{$n|intval}
							{else}
								0
							{/if}
						</td>

						<td class="center hidden-xs"  title="{l s='How many times the list has been displayed ' mod='blockwishlistpro'}">{$wishlists[i].counter|intval}</td>

						<td class="center"  title="{l s='Created on ... ' mod='blockwishlistpro'}">{$wishlists[i].date_add|escape:'htmlall':'UTF-8'}</td>

						<td class="center" style="width:20px;">
						<a href="{$link->getModuleLink('blockwishlistpro', 'view', ['token'=>{$wishlists[i].token|escape:'htmlall':'UTF-8'}|escape:'htmlall':'UTF-8'])|escape:'htmlall':'UTF-8'}" target="_blank" title="{l s='Go to the list web page like a donator' mod='blockwishlistpro'}">{l s='Link' mod='blockwishlistpro'}</a>
						</td>

						<td class="center">
							<a href="javascript:;" onclick='return (WishlistDelete("wishlist_{$wishlists[i].id_wishlist|intval}", "{$wishlists[i].id_wishlist|intval}", $("#delete_confirm").html(),
								$("#delete_impossible").html()));' title="{l s='Delete this list' mod='blockwishlistpro'}"><img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/delete.gif" alt="{l s='Delete' mod='blockwishlistpro'}" /></a>
						</td>

					</tr>
				{/section}
				</tbody>
			</table>
		</div>

		<div id="block-order-detail">&nbsp;</div>

		{/if}

	{/if}
{*
	<ul class="footer_links">
		<li><a href="{$link->getPageLink('my-account', true)}"><img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/my-account.gif" alt="" class="icon" /></a><a href="{$link->getPageLink('my-account', true)}">{l s='Back to Your Account' mod='blockwishlistpro'}</a></li>
		<li><a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}"><img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/home.gif" alt="" class="icon" /></a><a href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}">{l s='Home' mod='blockwishlistpro'}</a></li>
	</ul>
*}
</div>
