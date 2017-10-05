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
{if !isset($content_only) || !$content_only}
					</div><!-- #center_column -->
					{if isset($right_column_size) && !empty($right_column_size)}
						<div id="right_column" class="col-xs-12 col-sm-{$right_column_size|intval} column">{$HOOK_RIGHT_COLUMN}</div>
					{/if}
					</div><!-- .row -->
				</div><!-- #columns -->
			</div><!-- .columns-container -->
{if $page_name=="index" || $page_name=="product" || $page_name=="category"}
	<section class="savoir-faire">
		<div class="container">
			<h2>{l s="savoir-faire titre"}</h2>
			<h3>{l s="savoir-faire slogan"}</h3>
			<a class="btn-noir" href="/content/qui-sommes-nous-1">{l s="En savoir plus"}</a>
		</div>
	</section>
{/if}
			{if isset($HOOK_FOOTER)}
				<!-- Footer -->
				<div class="footer-container">
					<footer id="footer"  class="container">
						<div class="row">
						{$HOOK_FOOTER}
						
						{*<section class="categories">
							<ul>
								<li>{l s="joaillerie"}</li>
								<li>{l s="montres femmes"}</li>
								<li>{l s="montres hommes"}</li>
								<li>{l s="nos marques"}</li>
								<li>{l s="boutique rolex"}</li>
							</ul>
						</section>*}
						</div>
					</footer>
			<div class="footer-bottom">
				<div class="contain">
					<ul>
						<li>{l s="©2017 Boutique les montres"}</li>
						<li class="tiret">{l s="Crédits"}</li>
						<li class="tiret">{l s="Tous droits réservés"}</li>
						<li class="tiret"><a href="/content/2-mentions-legales">{l s="Mentions légales"}</a></li>
						<li class="tiret"><a href="/plan-site">{l s="sitemap"}</a></li>
					</ul>
				</div>
			</div>
				</div><!-- #footer -->
			{/if}
		</div><!-- #page -->
{/if}
{include file="$tpl_dir./global.tpl"}
	</body>
</html>