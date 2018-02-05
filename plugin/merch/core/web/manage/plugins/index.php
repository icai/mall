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
		global $_GPC;
		$category = m('plugin')->getList(1);
		$has_plugins = array();

		if (p('exhelper')) {
			$has_plugins[] = 'exhelper';
		}


		if (p('taobao')) {
			$has_plugins[] = 'taobao';
		}


		$plugins_list = array();

		foreach ($category as $key => $value ) {
			foreach ($value['plugins'] as $k => $v ) {
				if (in_array($v['identity'], $has_plugins)) {
					$plugins_list[] = $v;
				}

			}
		}

		include $this->template();
	}
}


?>