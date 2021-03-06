{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{capture name=path}{l s='Our stores'}{/capture}

<h1 class="">
	{l s='Our stores'}
</h1>
<h2>{l s="texte magasins"}</h2>
{if $simplifiedStoresDiplay}
	<div id="map"></div>
	{if $stores|@count}
		<p class="store-title">
			<strong class="dark">
				{l s='Here you can find our store locations. Please feel free to contact us:'}
			</strong>
		</p>
	    <table class="table table-bordered">
	    	<thead>
            	<tr>
                	<th class="logo">{l s='Logo'}</th>
                    <th class="name">{l s='Store name'}</th>
                    <th class="address">{l s='Store address'}</th>
                    <th class="store-hours">{l s='Working hours'}</th>
                </tr>
            </thead>
			{foreach $stores as $store}
				<tr class="store-small">
					<td class="logo">
						{if $store.has_picture}
							<div class="store-image">
								<img src="{$img_store_dir}{$store.id_store}-medium_default.jpg" alt="{$store.name|escape:'html':'UTF-8'}" width="{$mediumSize.width}" height="{$mediumSize.height}"/>
							</div>
						{/if}
					</td>
					<td class="name">
						{$store.name|escape:'html':'UTF-8'}
					</td>
		            <td class="address">
		            {assign value=$store.id_store var="id_store"}
		            {foreach from=$addresses_formated.$id_store.ordered name=adr_loop item=pattern}
	                    {assign var=addressKey value=" "|explode:$pattern}
	                    {foreach from=$addressKey item=key name="word_loop"}
	                        <span {if isset($addresses_style[$key])} class="{$addresses_style[$key]}"{/if}>
	                            {$addresses_formated.$id_store.formated[$key|replace:',':'']|escape:'html':'UTF-8'}
	                        </span>
	                    {/foreach}
	                {/foreach}
	                	<br/>
						{if $store.phone}<br/>{l s='Phone:'} {$store.phone|escape:'html':'UTF-8'}{/if}
						{if $store.fax}<br/>{l s='Fax:'} {$store.fax|escape:'html':'UTF-8'}{/if}
						{if $store.email}<br/>{l s='Email:'} {$store.email|escape:'html':'UTF-8'}{/if}
						{if $store.note}<br/><br/>{$store.note|escape:'html':'UTF-8'|nl2br}{/if}
					</td>
		            <td class="store-hours">
						{if isset($store.working_hours)}{$store.working_hours}{/if}
		            </td>
				</tr>
			{/foreach}
	    </table>
	{/if}
{strip}
{addJsDef map=''}
{addJsDef markers=array()}
{addJsDef infoWindow=''}
{addJsDef locationSelect=''}
{addJsDef defaultLat=$defaultLat}
{addJsDef defaultLong=$defaultLong}
{addJsDef hasStoreIcon=$hasStoreIcon}
{addJsDef distance_unit=$distance_unit}
{addJsDef img_store_dir=$img_store_dir}
{addJsDef img_ps_dir=$img_ps_dir}
{addJsDef searchUrl=$searchUrl}
{addJsDef logo_store=$logo_store}
{addJsDefL name=translation_1}{l s='No stores were found. Please try selecting a wider radius.' js=1}{/addJsDefL}
{addJsDefL name=translation_2}{l s='store found -- see details:' js=1}{/addJsDefL}
{addJsDefL name=translation_3}{l s='stores found -- view all results:' js=1}{/addJsDefL}
{addJsDefL name=translation_4}{l s='Phone:' js=1}{/addJsDefL}
{addJsDefL name=translation_5}{l s='Get directions' js=1}{/addJsDefL}
{addJsDefL name=translation_6}{l s='Not found' js=1}{/addJsDefL}
{/strip}
{else}
	<div id="map"></div>
	{if $stores|@count}
	{assign var="cle" value="1"}
			{foreach from=$stores item=store}
<br>
			<div class="cms-content">
				<div class="bloc{$cle} cms-bloc">
				
					<img src="{$img_store_dir}{$store.id_store}.jpg" alt="{$store.name|escape:'html':'UTF-8'}" />
				</div>
				<div class="cms-bloc bloc-text">
						<h3>{$store.name|escape:'html':'UTF-8'}</h3>
						{if $store.note}{$store.note|escape:'html':'UTF-8'|nl2br}{/if}
						
						<div class="store-info">
							<div class="line adresse">
							{$store.address1}<br>
							{$store.postcode} {$store.city}
							</div>
							<div class="line tel">
							{if $store.phone}<br/>{l s='Phone:'} <a href="tel:{$store.phone}">{$store.phone|escape:'html':'UTF-8'}</a>{/if}
							</div>
							<div class="line open">
							{foreach from=$store item=key key=id}
								{if $id=="hours"}
									{foreach from=$key|unserialize item=val}
									{$val}
									{/foreach}
								{/if}
							{/foreach}
							</div>
						</div>

				</div>
			</div>
			{assign var="cle" value=$cle*-1}
			{/foreach}
	{/if}
    <div class="store-content-select selector3">
    	<select id="locationSelect" class="form-control">
    		<option>-</option>
    	</select>
    </div>

{strip}
{addJsDef map=''}
{addJsDef markers=array()}
{addJsDef infoWindow=''}
{addJsDef locationSelect=''}
{addJsDef defaultLat=$defaultLat}
{addJsDef defaultLong=$defaultLong}
{addJsDef hasStoreIcon=$hasStoreIcon}
{addJsDef distance_unit=$distance_unit}
{addJsDef img_store_dir=$img_store_dir}
{addJsDef img_ps_dir=$img_ps_dir}
{addJsDef searchUrl=$searchUrl}
{addJsDef logo_store=$logo_store}
{addJsDefL name=translation_1}{l s='No stores were found. Please try selecting a wider radius.' js=1}{/addJsDefL}
{addJsDefL name=translation_2}{l s='store found -- see details:' js=1}{/addJsDefL}
{addJsDefL name=translation_3}{l s='stores found -- view all results:' js=1}{/addJsDefL}
{addJsDefL name=translation_4}{l s='Phone:' js=1}{/addJsDefL}
{addJsDefL name=translation_5}{l s='Get directions' js=1}{/addJsDefL}
{addJsDefL name=translation_6}{l s='Not found' js=1}{/addJsDefL}
{/strip}
{/if}
		<section class="print-social">
							<!-- usefull links-->
				<ul id="usefull_link_block" class="clearfix no-print">
						<li class="send"><a href="mailto:?body={$base_dir}{$smarty.server.REQUEST_URI}">{l s="Envoyer à un ami"}</a></li>
					<li class="print">
						<a href="javascript:print();">
							imprimer
						</a>
					</li>
				</ul>
						
	<p class="socialsharing_product list-inline no-print">
							<button data-type="facebook" type="button" class="btn btn-default btn-facebook social-sharing">
				<i class="icon-facebook"></i> 
				<!-- <img src="http://les-montres2.dev/modules/socialsharing/img/facebook.gif" alt="Facebook Like" /> -->
			</button>
									<button data-type="pinterest" type="button" class="btn btn-default btn-pinterest social-sharing">
				<i class="icon-pinterest"></i> 
				<!-- <img src="http://les-montres2.dev/modules/socialsharing/img/pinterest.gif" alt="Pinterest" /> -->
			</button>
			</p>
			<span class="partage">partager</span>
		</section>
