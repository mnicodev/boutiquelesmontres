{if $page_name=="index"}
<div id="les_entrees">
{assign var=id value=0}
{foreach from=$categories item=cat}
<div class="entre" style="background: url(/img/c/{$cat.id_category}.jpg) no-repeat;background-size:cover;background-position: top right">
<a href="/{$cat.link_rewrite}-{$cat.id_category}">
<img src="/img/trans.png" class="cover" />
</a>
<div class="bloc_hover">
	<div class="contain">
		<img src="/img/{$cat.id_category}-logo.png" class="logo-cat" />
		<h2>{$cat.description|truncate:200:"..."}</h2>
		<a href="/{$cat.link_rewrite}-{$cat.id_category}" class="btn-noir">Découvrir</a>
	</div>
</div>

</div>
{assign var=id value=$di+1}
{/foreach}
</div>
{/if}
