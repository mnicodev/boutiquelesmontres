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

<div id="results_searchlist">
	<h2>{l s='Showing wishlists results for' mod='blockwishlistpro'} "<span> {$request|escape:'htmlall':'UTF-8'}</span>"</h2>
	{if isset($request) && $request}
		{if isset($customers) && $customers && $customers|@count >= 1}
			<table border='0' >
				<thead><tr>
					<th>{l s='First name' mod='blockwishlistpro'}</th>
					<th>{l s='Last name' mod='blockwishlistpro'}</th>
					<th>{l s='List name - Link' mod='blockwishlistpro'}</th>
					<th>{l s='Created' mod='blockwishlistpro'}</th>
				</tr></thead>
				<tbody>
				{foreach from=$customers item=wishlist name=i}
					<tr></tr>
					<tr>
					<td>{$wishlist.firstname|escape:'htmlall':'UTF-8'}</td>
						<td>{$wishlist.lastname|escape:'htmlall':'UTF-8'}</td>
						<td><a href="{$link->getModuleLink('blockwishlistpro', 'view', ['token'=>{$wishlist.token|escape:'htmlall':'UTF-8'}])|escape:'htmlall':'UTF-8'}" target="_blank" title="{l s='Go to the list web page like a donator' mod='blockwishlistpro'}" class="underline">{$wishlist.list_name|escape:'htmlall':'UTF-8'}</a></td>
						<td>{$wishlist.date_add|date_format:$date_format|escape:'htmlall':'UTF-8'}</td>
						</tr>
				{/foreach}
				</tbody></table>
		{else}
			<p>
			{l s='Sorry, no result for' mod='blockwishlistpro'} <strong> {$request|escape:'htmlall':'UTF-8'}</strong>
			</p>
		{/if}
	{else}
		<p>
		{l s='The search request is empty. Please enter the last name of the creator of the list, or a part of it. For example \'smi\' for \'smith\'' mod='blockwishlistpro'}
		</p>
	{/if}

	<br /><br /><br />
	<div id="searchdivd">
		<form method="post" action="{$search_link|escape:'htmlall':'UTF-8'}" name="searchform"  id="searchformd">
			<p><strong>{l s='New search' mod='blockwishlistpro'} ?</strong></p>
			<p>
				{l s='Search for a list by name' mod='blockwishlistpro'} <br /><br />
				<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/bullet.gif" alt="{l s='validate' mod='blockwishlistpro'}" height="7" width="10" />
				<input type="text" name="searchname" id="searchname2" value="&nbsp;{l s='enter last name' mod='blockwishlistpro'}" onfocus="this.value='';this.style.cssText='color:black';"/>
				<input type="hidden" name="id_lang" value="'.$id_lang.'" />
				<br /><br />
				{if $themeChoice eq 0}
				<input type="submit" name="searchsubmit" value="{l s='Search' mod='blockwishlistpro'}" id="searchsubmitd" class="exclusive"/>
				{else}
				<button type="submit" name="searchsubmit" class="btn btn-default button button-medium"><span>{l s='Search' mod='blockwishlistpro'}<i class="icon-search right"></i></span></button>
				{/if}
			</p>
		</form>
	</div>
</div>