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
	//后台查看业绩详细信息页面
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
	
	public function doMobileMobile()
	{
		m('route')->run(false);
	}
	  
	public function payResult($params)
	{
		return m('order')->payResult($params);
	}
	//移动端业绩提成页面
	public function doMobileShow()
	{	
		global $_W,$_GPC;
		//测试设定，需移除
		$_W['openid']="oYnP20ZPKqZcamozRDr9DmwH8U8o";
		$_W['uniacid']="6";

		if($_W['isajax']){
			if($_GPC['num']==0)
				return;
			$nickname=pdo_fetchcolumn('select nickname from ims_ewei_shop_member where openid=:openid and uniacid=:uniacid',array(':openid'=>$_W['openid'],':uniacid'=>$_W['uniacid']));
			pdo_insert('ewei_shop_withdraw',array('openid'=>$_W['openid'],'nickname'=>$nickname,'uniacid'=>$_W['uniacid'],'num'=>$_GPC['num'],'type'=>$_GPC['type'],'status'=>'0'));
			$res=pdo_fetch('select com2_total,profit_total,com2_withdraw,profit_withdraw from ims_ewei_shop_member where openid=:openid and uniacid=:uniacid',array(':openid'=>$_W['openid'],':uniacid'=>$_W['uniacid']));
			if($_GPC['type']==2){
			 pdo_update('ewei_shop_member',array('com2_total'=>$res['com2_total']-$_GPC['num'],'com2_withdraw'=>$res['com2_withdraw']+$_GPC['num']),array('openid'=>$_W['openid'],'uniacid'=>$_W['uniacid']));

			} 
			else if($_GPC['type']==1){

			pdo_update('ewei_shop_member',array('profit_total'=>$res['profit_total']-$_GPC['num'],'profit_withdraw'=>$res['profit_withdraw']+$_GPC['num']),array('openid'=>$_W['openid'],'uniacid'=>$_W['uniacid']));
			}
			return ;
		}
		$data=pdo_fetch('select * from '.tablename('ewei_shop_member').' where openid=:openid and uniacid=:uni limit 1 ',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));
		if($data['agentid']==0){
			$data['recommender']='总店';
		}
		else{
			$data['recommender']=pdo_fetchcolumn('select nickname from '.tablename('ewei_shop_member').' where id=:agentid and uniacid=:uni limit 1 ',array(':agentid'=>$data['agentid'],':uni'=>$_W['uniacid']));
		}
		
		$data['com_total']=number_format($data['com_total'],2);
		$data['profit_total']=number_format($data['profit_total'],2);
		$data['profit_withdraw']=number_format($data['profit_withdraw'],2);
		$data['profit_suc']=number_format($data['profit_suc'],2);
		$data['com2_total']=number_format($data['com2_total'],2);
		$data['com2_withdraw']=number_format($data['com2_withdraw'],2);
		$data['com2_suc']=number_format($data['com2_suc'],2);
		//是否为合伙人
		if($data['agentlevel']==0){
			$data['levelname']='普通等级';
		}
		else{
			$level_merch=pdo_fetch('select level_merch,levelname from '.tablename('ewei_shop_commission_level').' where uniacid=:uni and id=:id limit 1',array(':uni'=>$_W['uniacid'],':id'=>$data['agentlevel']));
			$data['levelname']=$level_merch['levelname'];
		}
		

		//合伙人Id查询名下购买记录
		
		
		if($level_merch['level_merch']==5){


			//创造合伙人业绩的详细记录，每笔下线的购买情况
			// $detail=pdo_fetchall('select * from '.tablename('ewei_shop_commission_log_stat').' where partnerid=:partnerid and uniacid=:uni',array(':partnerid'=>$data['id'],':uni'=>$_W['uniacid']));
			// foreach ($detail as $key => $value) {
			// 	$info[$key]['name']=pdo_fetchcolumn('select nickname from '.tablename('ewei_shop_member').' where uniacid=:uni and openid=:id limit 1',array(':uni'=>$_W['uniacid'],':id'=>$value['openid']));
			// 	$info[$key]['goodsname']=pdo_fetchcolumn('select title from '.tablename('ewei_shop_goods').' where uniacid=:uni and id=:id limit 1',array(':uni'=>$_W['uniacid'],':id'=>$value['goodsid']));
			// 	$info[$key]['price']=$value['money'];
			// 	$info[$key]['createtime']=$value['createtime'];
			// }

			$detail=pdo_fetchall('select * from ims_ewei_shop_withdraw where openid=:openid and uniacid=:uniacid',array(':openid'=>$_W['openid'],':uniacid'=>$_W['uniacid']));
			
		}
		else{
			$info['msg']='您没有相关记录！';
		}
		
		include $this->template('show');
	}
	//转赠逻辑，合伙人赠送核心代理逻辑
	public function doMobileTransfer()
	{
		global $_W,$_GPC;
		if($_W['ispost']){
			if(empty($_GPC['id'])){
				pdo_update('ewei_shop_member',array('credit2'=>$_GPC['from_num']),array('id'=>$_GPC['from']));
			pdo_update('ewei_shop_member',array('credit2'=>$_GPC['to_num']),array('id'=>$_GPC['to']));
			return 'success';
			}
			else{
				$agentlevel=pdo_fetchcolumn('select id from ims_ewei_shop_commission_level where level_merch=:level_merch and uniacid=:uniacid',array(":level_merch"=>1,":uniacid"=>$_W['uniacid']));
				pdo_update('ewei_shop_member',array('level_merch'=>1,'agentlevel'=>$agentlevel),array('id'=>$_GPC['id']));
				$agent_num=pdo_fetchcolumn('select agent_num from ims_ewei_shop_member where id=:id',array(':id'=>$_GPC['agentid']));
				pdo_update('ewei_shop_member',array('agent_num'=>$agent_num-1),array('id'=>$_GPC['agentid']));
				return 'bind success';
			}
			
		}
	}
	
	
	
	
}


?>