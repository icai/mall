<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}


require hunter_mall_PLUGIN . 'merch/core/inc/page_merch.php';
class Index_EweiShopV2Page extends MerchWebPage
{
	public function main()
	{
		global $_W;
		if (mcv('perm.role') && !empty($_W['accounttotal'])) {
			header('location: ' . merchUrl('perm/role'));
			exit();
			return NULL;
		}


		if (mcv('perm.user') && !empty($_W['accounttotal'])) {
			header('location: ' . merchUrl('perm/user'));
			exit();
			return NULL;
		}


		if (mcv('perm.log')) {
			header('location: ' . merchUrl('perm/log'));
			exit();
		}

	}
}


?>