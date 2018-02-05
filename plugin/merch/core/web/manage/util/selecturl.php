<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}


require hunter_mall_PLUGIN . 'merch/core/inc/page_merch.php';
class Selecturl_EweiShopV2Page extends MerchWebPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		$full = intval($_GPC['full']);
		include $this->template();
	}

	public function querygoods()
	{
		global $_W;
		global $_GPC;
		$type = trim($_GPC['type']);
		$kw = trim($_GPC['kw']);
		$full = intval($_GPC['full']);

		if (!empty($kw) && !empty($type)) {
			if ($type == 'good') {
				$goods = pdo_fetchall('SELECT id,title,productprice,marketprice,thumb,sales,unit FROM ' . tablename('ewei_shop_goods') . ' WHERE uniacid= :uniacid and merchid=:merchid and status=:status and deleted=0 AND title LIKE :title ', array(':title' => '%' . $kw . '%', ':merchid' => $_W['merchid'], ':uniacid' => $_W['uniacid'], ':status' => '1'));
				$goods = set_medias($goods, 'thumb');
			}
			 else if ($type == 'coupon') {
				$coupons = pdo_fetchall('select id,couponname,coupontype from ' . tablename('ewei_shop_coupon') . ' where couponname LIKE :title and uniacid=:uniacid and merchid=:merchid', array(':uniacid' => $_W['uniacid'], ':merchid' => $_W['merchid'], ':title' => '%' . $kw . '%'));
			}

		}


		include $this->template('util/selecturl_tpl');
	}
}


?>