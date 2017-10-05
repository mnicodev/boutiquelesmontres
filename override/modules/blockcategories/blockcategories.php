<?php
if (!defined('_PS_VERSION_'))
	exit;
class BlockCategoriesOverride extends BlockCategories
{



	public function hookFooter($params)
	{
		
		return parent::hookFooter($params);
	}
}