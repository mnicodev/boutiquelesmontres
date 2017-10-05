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

{if isset($products) && $products}
	{*define number of products per line in other page for desktop*}
		{assign var='nbItemsPerLine' value=3}
		{assign var='nbItemsPerLineTablet' value=2}
		{assign var='nbItemsPerLineMobile' value=3}
	{*define numbers of product per line in other page for tablet*}
	{assign var='nbLi' value=$products|@count}
	{math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
	{math equation="nbLi/nbItemsPerLineTablet" nbLi=$nbLi nbItemsPerLineTablet=$nbItemsPerLineTablet assign=nbLinesTablet}
	{* {if !$refresh} *}
	{assign var='al_qt' value={l s='Please note that the desired quantity should not be lower than the purchased quantity !' mod='blockwishlistpro'}|escape:'quotes'}
	{assign var='remov_imp' value={l s='Impossible to remove the product because it has already been purchased. Tip: to not see it anymore in the wishlist, put the same quantity in the list as in the purchased quantity.' mod='blockwishlistpro'}|escape:'quotes'}
	{assign var='remov_imp_delet' value={l s='Impossible to remove the product because it has already been purchased. It will not be displayed on the list page.' mod='blockwishlistpro'}|escape:'quotes'}

<hr /><br />
<p>&nbsp;{l s='Total Gifts of the list' mod='blockwishlistpro'} <strong style="color:#FF0000">{$name|escape:'htmlall':'UTF-8'}</strong> : {convertPrice price=$total_wl.total_products_wt|escape:'htmlall':'UTF-8'}</p>
	<br />
	<div class='{if $spy_ps16|intval eq 0 }mng_menu {/if}fl_aw mg_bottom12 col-xs-12 col-md-4 {if $themeChoice eq 1}theme1{/if}' title="{l s='Set up products parameters : quantity, priority, cancellation' mod='blockwishlistpro'}">
		<div id="hideBoughtProducts" class='div_wl' onclick="WishlistVisibilitypro('wlp_bought', 'BoughtProducts');">
			<div class="img_block" ><img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/manage_pdt.gif" /></div>
			<div class="sous_block">
				<span class="text_wl">{l s='Products management' mod='blockwishlistpro'}</span>
				{if $themeChoice eq 0}
				<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/less.gif" width="10" height="10" />
				{else}
				 <i class="icon-minus-square right"></i>
				{/if}
			</div>
		</div>
		<div id="showBoughtProducts" class='div_wl' onclick="WishlistVisibilitypro('wlp_bought', 'BoughtProducts');" style="display:none">
			<div class="img_block" ><img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/manage_pdt.gif" /></div>
			<div class="sous_block" >
				<span class="text_wl">{l s='Products management' mod='blockwishlistpro'}</span>
				{if $themeChoice eq 0}
				<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/more.gif" width="10" height="10" />
				{else}
				 <i class="icon-plus-square right"></i>
				{/if}
			</div>
		</div>
	</div>
	{if count($productsBoughts_actual)}
	<div class='{if $spy_ps16|intval eq 0 }mng_menu {/if}fl_aw mg_bottom12 col-xs-12 col-md-4 					{if $themeChoice eq 1}theme1{/if}' title="{l s='Look at the list of bought Gifts : product, quantity, donator, date' mod='blockwishlistpro'}">
		<div id="hideBoughtProductsInfos" class='div_wl' onclick="WishlistVisibilitypro('wlp_bought_infos', 'BoughtProductsInfos');" style="display:none">
			<div class="img_block" ><img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/binoculars.png"></div>
			<div class="sous_block" >
				<span class="text_wl">{l s='Offered gifts' mod='blockwishlistpro'}</span>
				{if $themeChoice eq 0}
				<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/less.gif" width="10" height="10" />
				{else}
				 <i class="icon-minus-square right"></i>
				{/if}
			</div>
		</div>
		<div id="showBoughtProductsInfos" class='div_wl' onclick="WishlistVisibilitypro('wlp_bought_infos', 'BoughtProductsInfos');">
			<div class="img_block" ><img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/binoculars.png" /></div>
			<div class="sous_block" >
				<span class="text_wl">{l s='Offered gifts' mod='blockwishlistpro'}</span>
				{if $themeChoice eq 0}
				<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/more.gif" width="10" height="10" />
				{else}
				 <i class="icon-plus-square right"></i>
				{/if}
			</div>
		</div>
	</div>
	{/if}
	<div class='{if $spy_ps16|intval eq 0 }mng_menu {/if}
fl_aw mg_bottom12 col-xs-12 col-md-4' title="{l s='Send the list webpage address by email. Add your personal message to the default message' mod='blockwishlistpro'}">
		<div id="showSendWishlist" class='div_wl' onclick="WishlistVisibilitypro('wl_send', 'SendWishlist');">
			<div class="img_block" ><img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/sendtoafriend.png"></div>
			<div class="sous_block" >
				<span class="text_wl">{l s='Send the wishlist address' mod='blockwishlistpro'}</span>
				{if $themeChoice eq 0}
				<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/more.gif" />
				{else}
				 <i class="icon-plus-square right"></i>
				{/if}
			</div>
		</div>
		<div id="hideSendWishlist" class='div_wl' onclick="WishlistVisibilitypro('wl_send', 'SendWishlist');" style="display:none">
			<div class="img_block" ><img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/sendtoafriend.png"></div>
			<div class="sous_block" >
				<span class="text_wl">{l s='Send the wishlist address' mod='blockwishlistpro'}</span>
				{if $themeChoice eq 0}
				<img src="{$content_dir|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/less.gif" width="10" height="10" />
				{else}
				 <i class="icon-minus-square right"></i>
				{/if}
			</div>
		</div>
	</div>
	<hr class='separationd'>
	<br />
{* {/if} *}
	<div class="wlp_bought">
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
		<ul class="product_list_aw grid{if $themeChoice eq 1} row modern1{else} theme_classic{/if}">
			{foreach from=$products item=product name=i}
	{*		{if $product.isobj eq 1}  if 0 no display because the product does not exist anymore*}

			{math equation="(total%perLine)" total=$smarty.foreach.i.total perLine=$nbItemsPerLine assign=totModulo}
			{math equation="(total%perLineT)" total=$smarty.foreach.i.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
			{math equation="(total%perLineT)" total=$smarty.foreach.i.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
			{if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
			{if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
			{if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}

			<li class="border_top1 address_dd {if $themeChoice eq 0 && $smarty.foreach.i.index % 2}alternate_{/if}item {if $themeChoice eq 1}col-xs-12 col-sm-6 col-md-4{if $smarty.foreach.i.iteration%$nbItemsPerLine == 0} last-in-line{elseif $smarty.foreach.i.iteration%$nbItemsPerLine == 1} first-in-line{/if}{if $smarty.foreach.i.iteration > ($smarty.foreach.i.total - $totModulo)} last-line{/if}{if $smarty.foreach.i.iteration%$nbItemsPerLineTablet == 0} last-item-of-tablet-line{elseif $smarty.foreach.i.iteration%$nbItemsPerLineTablet == 1} first-item-of-tablet-line{/if}{if $smarty.foreach.i.iteration%$nbItemsPerLineMobile == 0} last-item-of-mobile-line{elseif $smarty.foreach.i.iteration%$nbItemsPerLineMobile == 1} first-item-of-mobile-line{/if}{if $smarty.foreach.i.iteration > ($smarty.foreach.i.total - $totModuloMobile)} last-mobile-line{/if} borderstyl1{else}theme_classic{/if}" id="wlp_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}">
				<div class='product-container'>
					<ul>
					<li class="address_title">
						{if $product.isobj eq 1} {* isobj = 0 means a bo cancelled product but still in list *}
							{if $product.isobj_attr eq 1 AND $product.is_attr_exist eq 1}
						<a href="{$link->getProductlink($product.id_product, $product.link_rewrite, $product.category_rewrite)|escape:'htmlall':'UTF-8'}" title="{l s='Look at Product data sheet for more details' mod='blockwishlistpro'}" target="_blank" class="link_cyber">{$product.name|truncate:60:'...'|escape:'htmlall':'UTF-8'}</a>
							{elseif $product.isobj_attr eq 0 && $product.id_product_attribute neq 0}
								{$product.name|truncate:60:'...'|escape:'htmlall':'UTF-8'} id_product {$product.id_product|escape:'htmlall':'UTF-8'} id_product_attribute {$product.id_product_attribute|escape:'htmlall':'UTF-8'} <strong class="alert_red">{l s='doesn\'t exist anymore' mod='blockwishlistpro'}</strong>. {l s='Please go to the product page and add it to your list if you wish.' mod='blockwishlistpro'}
							{elseif $product.is_attr_exist eq 0 && $product.isobj_attr eq 1}
								{l s='This combination' mod='blockwishlistpro'} ({l s='product' mod='blockwishlistpro'} :  {$product.name|truncate:60:'...'|escape:'htmlall':'UTF-8'} id_product {$product.id_product|escape:'htmlall':'UTF-8'} id_product_attribute {$product.id_product_attribute|escape:'htmlall':'UTF-8'}) <strong class="alert_red">{l s='doesn\'t exist anymore' mod='blockwishlistpro'}</strong> {l s=' but exists with others combinations' mod='blockwishlistpro'}. {l s='Please look at new combinations and add some in your list if needed be.' mod='blockwishlistpro'}
							{/if}
						{else}
						{$product.name|truncate:60:'...'|escape:'htmlall':'UTF-8'} id_product {$product.id_product|escape:'htmlall':'UTF-8'} <strong class="alert_red">{l s='doesn\'t exist anymore' mod='blockwishlistpro'}</strong>
						{/if}
					</li>
					<li class="address_name {if $themeChoice eq 1}border_right1{/if}">
						<div class="align_dd {if $themeChoice eq 1}col-xs-6{else}theme_classic{/if}">
							<span class="price">{convertPrice price=$product.price_dd}</span>
							<a href="{$link->getProductlink($product.id_product, $product.link_rewrite, $product.category_rewrite)|escape:'htmlall':'UTF-8'}" title="{l s='Look at Product data sheet for more details' mod='blockwishlistpro'}" target="_blank" style="display:block">
								{if $product.have_image}
									<img src="{$link->getImageLink($product.link_rewrite, $product.cover, $type_medium)|escape:'htmlall':'UTF-8'}" alt="{$product.name|escape:'htmlall':'UTF-8'}" class="replace-2x img-responsive"/>
								{else}
									<img src="{$img_prod_dir|escape:'htmlall':'UTF-8'}{$lang_iso|escape:'htmlall':'UTF-8'}-default-large_default.jpg" alt="{$product.name|escape:'htmlall':'UTF-8'}" title="{$product.name|escape:'htmlall':'UTF-8'}" width="{$mediumSize.width|escape:'htmlall':'UTF-8'}" height="{$mediumSize.height|escape:'htmlall':'UTF-8'}" class="replace-2x img-responsive"/>
								{/if}
							</a>
							{if $product.id_product_attribute AND isset($product.attributes_small)}	{* important to test product.id_product_attribute because of bug prestashop (1.4.4.0) which takes an attribute for a non attribute product *}
							<div rel="decl">
								<a href="{$link->getProductlink($product.id_product, $product.link_rewrite, $product.category_rewrite)|escape:'htmlall':'UTF-8'}"  title="{l s='Look at Product data sheet for more details' mod='blockwishlistpro'}" target="_blank" class="link_cyber">
								{$product.attributes_small|escape:'htmlall':'UTF-8'}</a>
							</div>
							{/if}
							<hr style="clear:both; visibility:hidden" />
						</div>

						<div class="wishlist_product_detail_dd {if $themeChoice eq 1}col-xs-6 theme1 {else}theme_classic{/if}">
							<ul>
								<li title="{l s='You can change the quantity if you wish. Once you have changed it do not forget to click on \'save\'.' mod='blockwishlistpro'}">
									{l s='List quantity' mod='blockwishlistpro'}
									&nbsp;
									<input type="text" id="quantity_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}" value="{$product.quantity_init|intval}" size="3"  style="border: 1px solid #BDC2C9;" title="{l s='You can change the quantity if you wish. Once you have changed it do not forget to click on \'save\'.' mod='blockwishlistpro'}"/>
								</li>
								<li>
									{l s='Bought quantity' mod='blockwishlistpro'}&nbsp;
									<input type="text" value="{$product.bought_qty_actual|intval}"size="3" disabled="disabled" style="color:#DD2A81;border: 1px solid #BDC2C9; background-color:#DDDDDD" />
								</li>
								<li>
									{l s='Left quantity' mod='blockwishlistpro'}&nbsp;<input type="text" id="quantity_left_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}" value="{$product.left|intval}" size="3" disabled="disabled" style="border: 1px solid #BDC2C9;background-color:#DDDDDD"/>
								</li>
								{* {if $product.isobj eq 1 AND $product.isobj_attr eq 1 AND $product.is_attr_exist eq 1} *}
								<li title="{l s='Choose the priority to sort products on the web page of the list (top=first)' mod='blockwishlistpro'} - {l s='Top = First' mod='blockwishlistpro'}">{l s='Priority' mod='blockwishlistpro'}&nbsp;
									<select id="priority_{$product.id_product|intval}_{$product.id_product_attribute|intval}" >
									<option value="0"{if $product.priority eq 0} selected="selected"{/if}>{l s='Top' mod='blockwishlistpro'}</option>
									<option value="1"{if $product.priority eq 1} selected="selected"{/if}>{l s='Very Important' mod='blockwishlistpro'}</option>
									<option value="2"{if $product.priority eq 2} selected="selected"{/if}>{l s='Important' mod='blockwishlistpro'}</option>
									<option value="3"{if $product.priority eq 3} selected="selected"{/if}>{l s='Medium' mod='blockwishlistpro'}</option>
									<option value="4"{if $product.priority eq 4} selected="selected"{/if}>{l s='Low' mod='blockwishlistpro'}</option>
									</select>
								</li>
								<li class='clearfix mg_top12'>
									<a href="javascript:;" rel="save" class="{if $themeChoice eq 0}exclusive save_right{else}btn btn-default button button-small pull-right{/if}" onclick="WishlistProductManage(
									'wlp_bought_{$product.id_product_attribute|intval}',
									'update',
									'{$id_wishlist|intval}',
									'{$product.id_product|intval}',
									'{$product.id_product_attribute|intval}',
									$('#quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}').val(),
									'quantity_left_{$product.id_product|intval}_{$product.id_product_attribute|intval}',
									'quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}',
									$('#priority_{$product.id_product|intval}_{$product.id_product_attribute|intval}').val(),
									'{$al_qt|escape:'htmlall':'UTF-8'}',
									{if $product.isobj eq 0 OR $product.is_attr_exist eq 0}'{$remov_imp_delet|escape:'htmlall':'UTF-8'}'{else}'{$remov_imp|escape:'htmlall':'UTF-8'}'{/if}
									);
									return false;"
									title="{l s='Save' mod='blockwishlistpro'}"><span>{l s='Save' mod='blockwishlistpro'}</span></a>
								</li>
								<li>
									<br />
									<a href="javascript:;" rel="delete" class="{if $themeChoice eq 0}clear{else}btn btn-default button-small{/if}" onclick="WishlistProductManage(
									'wlp_bought',
									'delete',
									'{$id_wishlist|intval}',
									'{$product.id_product|intval}',
									'{$product.id_product_attribute|intval}',
									$('#quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}').val(),
									'',
									'',
									$('#priority_{$product.id_product|intval}_{$product.id_product_attribute|intval}').val(),
									'{$al_qt|intval}',
									{if $product.isobj eq 0 OR $product.is_attr_exist eq 0}'{$remov_imp_delet|escape:'htmlall':'UTF-8'}'{else}'{$remov_imp|escape:'htmlall':'UTF-8'}'{/if}
									);return false;"
									title="{l s='Delete' mod='blockwishlistpro'}. {l s='The product will be removed from the list' mod='blockwishlistpro'}"><span>{l s='Delete' mod='blockwishlistpro'}{if $themeChoice eq 1} <i class='icon-trash-o'></i>{/if}</span></a>
								</li>
							</ul>
						</div>

						<hr style="clear:both; visibility:hidden">
					</li>
					</ul>
				</div>{* end div  class="product-container" *}
			</li>
	{*		{/if}	end if $product.isobj eq 1*}
			{/foreach}
		</ul> {* end ul  class="wlp_bought" *}
	</div>
	<div class="clear"></div>
	<br />
{*	{if !$refresh} *}
		<form class="wl_send std hidden_wl" method="post" onsubmit="return (false);">
			{l s='Link to the list' mod='blockwishlistpro'} : <a href="{$link->getModuleLink('blockwishlistpro', 'view', ['token'=>{$token|escape:'htmlall':'UTF-8'}])|escape:'htmlall':'UTF-8'}" target="_blank" style="color:#0000FF" title="{l s='Go to the list web page like a donator' mod='blockwishlistpro'}"><br>{$link->getModuleLink('blockwishlistpro', 'view', ['token'=>{$token|escape:'htmlall':'UTF-8'}])|escape:'htmlall':'UTF-8'}</a><br /><br />
			<fieldset>
	<!-- new DD -->
				<strong>{l s='Recipients\' emails' mod='blockwishlistpro'}</strong>
				<p class="required">
					<label for="email1" style="width:55px;text-align:left">{l s='Email' mod='blockwishlistpro'}1</label>
					<sup>*</sup><input type="text" name="email1" id="email1"  style="background-color:#e7fAeD" size="55"/>
				</p>
				{section name=i loop=11 start=2}
				<p>
					<label for="email{$smarty.section.i.index|intval}" style="width:55px;text-align:left">{l s='Email' mod='blockwishlistpro'}{$smarty.section.i.index|intval}</label>&nbsp;
					<input type="text" name="email{$smarty.section.i.index|intval}" id="email{$smarty.section.i.index|intval}" size="55" style="background-color:#e7fAeD"/>
				</p>
				{/section}
				<p class="required">
					<sup>*</sup>
					{l s='Required field' mod='blockwishlistpro'}
				</p>

				<div id="ordermsg">
							<p></p>
							<p class="textarea">
								<strong>{l s='Personal Message (optional)' mod='blockwishlistpro'}</strong> - {l s='Will be inserted in the default message.' mod='blockwishlistpro'}
								<textarea cols="60" rows="3" name="message_personal"  id="id_message_personal"></textarea>
							</p>
				</div>
				<input type="hidden" id="mail_requ" value="{l s='First mail required. Please fill in the field' mod='blockwishlistpro'}" />
				<input type="hidden" id="mail_sent" value="{l s='The email has been sent successfully' mod='blockwishlistpro'}" />
				<input type="hidden" id="mail_not_sent" value="{l s='Cannot send the email. Please check sending parameters or try later on' mod='blockwishlistpro'}" />
				<input type="hidden" id="mail_invalid" value="{l s='Invalid email address' mod='blockwishlistpro'}" />
				<!-- email template -->
				<div class="email_template">
					<p title="{l s='Proceed in two steps : FIRST click on ' mod='blockwishlistpro'} {l s='1.Create email' mod='blockwishlistpro'}. {l s='THEN click on' mod='blockwishlistpro'} {l s='2.View email in a new sheet' mod='blockwishlistpro'} "> {l s='To view your e-mail before sending, click on 1 and 2 below ' mod='blockwishlistpro'}
					<img src='{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/views/img/icon/information.png' style="display:inline" alt="{l s='Proceed in two steps : FIRST click ' mod='blockwishlistpro'} \'{l s='1.Create email ' mod='blockwishlistpro'}\'. {l s='THEN click ' mod='blockwishlistpro'} \'{l s='2.View email in a new sheet' mod='blockwishlistpro'}\'" title="{l s='Proceed in two steps : FIRST click on ' mod='blockwishlistpro'} {l s='1.Create email' mod='blockwishlistpro'}. {l s='THEN click ' mod='blockwishlistpro'} {l s='2.View email in a new sheet' mod='blockwishlistpro'} "/> 				</p>
					<a class="a_email_template"  id="create_templ" href="#" title="{l s='First step' mod='blockwishlistpro'}" onclick="findOutEmail('{$id_wishlist|escape:'htmlall':'UTF-8'}', 'id_email', 'submit1', 'wishlist.html', 'wishlist-temp.html', $('#id_message_personal').val(), '{$modulename|escape:'htmlall':'UTF-8'}');"> {l s='1.Create email' mod='blockwishlistpro'}</a>
					<a id="view_templ_0" class="a_email_template" style='color:gray;' target="_blank" title='{l s='Second step' mod='blockwishlistpro'}' > {l s='2.View email (in a new sheet)' mod='blockwishlistpro'} </a>
					<a id="view_templ" class="a_email_template" href="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/{$modulename|escape:'htmlall':'UTF-8'}/mails/{$language|escape:'htmlall':'UTF-8'}/wishlist-temp.html" style='display:none; background-color:yellow;' target="_blank" title='{l s='Second step' mod='blockwishlistpro'}' > {l s='2.View email (in a new sheet)' mod='blockwishlistpro'} </a>
				</div>

				{if $themeChoice eq 0}
				<input class="button exclusive" type="button" value="{l s='Send' mod='blockwishlistpro'}" id="submit2" name="submitWishlist" onclick="WishlistSendpro('wl_send', '{$id_wishlist|escape:'htmlall':'UTF-8'}', 'email', 'submit2', $('#id_message_personal').val(), '{$modulename|escape:'htmlall':'UTF-8'}');" />
				{else}
				<button type="submit" name="submitWishlist" id="submit2" class="btn btn-default button button-medium pull-right"  onclick="WishlistSendpro('wl_send', '{$id_wishlist|escape:'htmlall':'UTF-8'}', 'email', 'submit2', $('#id_message_personal').val(), '{$modulename|escape:'htmlall':'UTF-8'}');"><span>{l s='Send' mod='blockwishlistpro'}<i class="icon-envelope-o right"></i></span></button>
				{/if}

			</fieldset>
		</form>
		{if count($productsBoughts_act_st)}
			<table class="wlp_bought_infos hidden_wl std">
				<thead>
					<tr>
						<th class="first_item">{l s='Product' mod='blockwishlistpro'}</td>
						<th class="item">{l s='Quantity' mod='blockwishlistpro'}</td>
						<th class="item">{l s='Offered by' mod='blockwishlistpro'}</td>
						<th class="last_item">{l s='Date' mod='blockwishlistpro'}</td>
					</tr>
				</thead>
				<tbody>
				{foreach from=$productsBoughts_act_st item=product name=i}
						<tr>
							<td class="first_item">
							<span style="float:left">
						{if $product.have_image}
							<img src="{$link->getImageLink($product.link_rewrite, $product.cover, $type_medium)|escape:'htmlall':'UTF-8'}" alt="{$product.name|escape:'htmlall':'UTF-8'}" class="replace-2x img-responsive"/>
						{else}
							<img src="{$img_prod_dir|escape:'htmlall':'UTF-8'}{$lang_iso|escape:'htmlall':'UTF-8'}-default-large_default.jpg" alt="{$product.name|escape:'htmlall':'UTF-8'}" title="{$product.name|escape:'htmlall':'UTF-8'}" width="{$mediumSize.width|escape:'htmlall':'UTF-8'}" height="{$mediumSize.height|escape:'htmlall':'UTF-8'}" class="replace-2x img-responsive"/>
						{/if}
							</span>
							<span style="float:left; margin-left:3px; width:150px">{$product.name|truncate:40:'...'|escape:'htmlall':'UTF-8'}
							{if $product.id_product_attribute AND isset($product.attributes_small)}	{* important to test product.id_product_attribute because of bug prestashop (1.4.4.0) which takes an attribute for a non attribute product *}
								<br /><i>{$product.attributes_small|escape:'htmlall':'UTF-8'}</i>
							{/if}</span>
							</td>
							<td class="item align_center">{$product.actual_qty|intval}</td>
							<td class="item align_center">{$product.firstname|escape:'htmlall':'UTF-8'} {$product.lastname|escape:'htmlall':'UTF-8'}</td>
							<td class="last_item align_center">{$product.date_add|escape:'htmlall':'UTF-8'}</td>
						</tr>
				{/foreach}
				</tbody>
			</table>
		{else}
			<p class="wlp_bought_infos hidden_wl std">{l s='No products offered at this time' mod='blockwishlistpro'}</p>
		{/if}
{*	{/if} *} {* end if refresh *}
{else}
	<b /><strong style="color:#FF0000">{l s='No products added to this wishlist' mod='blockwishlistpro'} : {$name|escape:'htmlall':'UTF-8'}</strong>
{/if}
