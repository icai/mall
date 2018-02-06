<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}


require_once IA_ROOT . '/addons/hunter_mall/version.php';
require_once IA_ROOT . '/addons/hunter_mall/defines.php';
require_once hunter_mall_INC . 'functions.php';
class hunter_mallModuleSite extends WeModuleSite
{
	public function getMenus()
	{
		global $_W;
		return array(
	array('title' => '管理后台', 'icon' => 'fa fa-shopping-cart', 'url' => webUrl())
	);
	}

	public function doWebWeb()
	{
		m('route')->run();
	}
	public function doWebDetail()
	{ 
		global $_GPC,$_W;
		$data=pdo_fetch('select * from '.tablename('ewei_shop_member').' where id=:id and uniacid=:uni limit 1 ',array(':id'=>$_GPC['id'],':uni'=>$_W['uniacid']));
		$data['com_total']=number_format($data['com_total'],2);
		$data['profit_total']=number_format($data['profit_total'],2);
		//是否为合伙人
		$level_merch=pdo_fetchcolumn('select level_merch from '.tablename('ewei_shop_commission_level').' where uniacid=:uni and id=:id limit 1',array(':uni'=>$_W['uniacid'],':id'=>$data['agentlevel']));
		//合伙人Id查询名下购买记录

		if($level_merch==5){
			$detail=pdo_fetchall('select * from '.tablename('ewei_shop_commission_log_stat').' where partnerid=:partnerid and uniacid=:uni',array(':partnerid'=>$data['id'],':uni'=>$_W['uniacid']));
			foreach ($detail as $key => $value) {
				$info[$key]['name']=pdo_fetchcolumn('select nickname from '.tablename('ewei_shop_member').' where uniacid=:uni and openid=:id limit 1',array(':uni'=>$_W['uniacid'],':id'=>$value['openid']));
				$info[$key]['goodsname']=pdo_fetchcolumn('select title from '.tablename('ewei_shop_goods').' where uniacid=:uni and id=:id limit 1',array(':uni'=>$_W['uniacid'],':id'=>$value['goodsid']));
				$info[$key]['price']=$value['money'];
				$info[$key]['createtime']=$value['createtime'];
			}

		}
		else{
			
			return '您没有相关记录！';
		}

		$result='<ul>';

		foreach ($info as $key => $value) {
			$result.="<li><font color='orange' >".$value['name'].'</font>在'.date('Y-m-d H:m',$value['createtime']).'购买'.$value['goodsname'].'金额'.$value['price'].'</li>';
		}
		$result.='</ul>';
		return $result;
	}
	public function doWebChange()
	{
		global $_GPC,$_W;
		if($_GPC['status']=='1'){
			include IA_ROOT . '/addons/hunter_mall/template/web/commission/status1.html';
		}
		else{
		include $this->template('commission/status2');
		}
	}
	public function doMobileMobile()
	{
		m('route')->run(false);
	}
	  
	public function payResult($params)
	{
		return m('order')->payResult($params);
	}
	public function doMobileShow()
	{	
		global $_W,$_GPC;
		$_W['openid']='oYnP20UyPqXsWNWpgp2_18UghPd4';

		if($_W['isajax']){
			$nickname=pdo_fetchcolumn('select nickname from ims_ewei_shop_member where openid=:openid',array(':openid'=>$_W['openid']));
			pdo_insert('ewei_shop_withdraw',array('openid'=>$_W['openid'],'nickname'=>$nickname,'uniacid'=>$_W['uniacid'],'num'=>$_GPC['num'],'type'=>$_GPC['type'],'status'=>'0'));
			$res=pdo_fetch('select com_total,profit_total,com_withdraw,profit_withdraw from ims_ewei_shop_member where openid=:openid and uniacid=:uniacid',array(':openid'=>$_W['openid'],':uniacid'=>$_W['uniacid']));
			if($_GPC['type']=0){
			 pdo_update('ewei_shop_member',array('com_total'=>$res['com_total']-$_GPC['num'],'com_withdraw'=>$res['com_withdraw']+$_GPC['num']),array('openid'=>$_W['openid'],'uniacid'=>$_W['uniacid']));

			} 
			else{
			pdo_update('ewei_shop_member',array('profit_total'=>$res['profit_total']-$_GPC['num'],'profit_withdraw'=>$res['profit_withdraw']+$_GPC['num']),array('openid'=>$_W['openid'],'uniacid'=>$_W['uniacid']));
			}
			return ;
		}
		$data=pdo_fetch('select * from '.tablename('ewei_shop_member').' where openid=:openid and uniacid=:uni limit 1 ',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));
		$data['com_total']=$data['com_total']*0.03;
		$data['com_total']=number_format($data['com_total'],2);
		$data['profit_total']=number_format($data['profit_total'],2);
		$data['profit_withdraw']=number_format($data['profit_withdraw'],2);
		$data['profit_suc']=number_format($data['profit_suc'],2);
		//是否为合伙人
		$level_merch=pdo_fetchcolumn('select level_merch from '.tablename('ewei_shop_commission_level').' where uniacid=:uni and id=:id limit 1',array(':uni'=>$_W['uniacid'],':id'=>$data['agentlevel']));
		//合伙人Id查询名下购买记录

		if($level_merch==5){


			//创造合伙人业绩的详细记录，每笔下线的购买情况
			// $detail=pdo_fetchall('select * from '.tablename('ewei_shop_commission_log_stat').' where partnerid=:partnerid and uniacid=:uni',array(':partnerid'=>$data['id'],':uni'=>$_W['uniacid']));
			// foreach ($detail as $key => $value) {
			// 	$info[$key]['name']=pdo_fetchcolumn('select nickname from '.tablename('ewei_shop_member').' where uniacid=:uni and openid=:id limit 1',array(':uni'=>$_W['uniacid'],':id'=>$value['openid']));
			// 	$info[$key]['goodsname']=pdo_fetchcolumn('select title from '.tablename('ewei_shop_goods').' where uniacid=:uni and id=:id limit 1',array(':uni'=>$_W['uniacid'],':id'=>$value['goodsid']));
			// 	$info[$key]['price']=$value['money'];
			// 	$info[$key]['createtime']=$value['createtime'];
			// }

			$detail=pdo_fetchall('select * from ims_ewei_shop_withdraw where openid=:openid',array(':openid'=>$_W['openid']));

		}
		else{
			$info['msg']='您没有相关记录！';
		}
		
		include $this->template('show');
	}
	
	
	
	
}


?>