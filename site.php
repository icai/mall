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
		// $_W['openid']="oYnP20ZPKqZcamozRDr9DmwH8U8o";
		// $_W['uniacid']="6";

		if($_W['isajax']){
			
			$nickname=pdo_fetchcolumn('select nickname from ims_ewei_shop_member where openid=:openid and uniacid=:uniacid',array(':openid'=>$_W['openid'],':uniacid'=>$_W['uniacid']));
			$res=pdo_fetchcolumn('select profit_suc,com2_suc,award_suc from ims_ewei_shop_member where openid=:openid and uniacid=:uniacid',array(':openid'=>$_W['openid'],':uniacid'=>$_W['uniacid']));
			if($_GPC['type']==1){
				$num= $_GPC['num']-$res['profit_suc'];
			}
			else if($_GPC['type']==2){
				$num=$_GPC['num']-$res['com2_suc'] ;
			}
			else if($_GPC['type']==3){
				$num=$_GPC['num']-$res['award_suc'] ;
			}
			pdo_insert('ewei_shop_withdraw',array('openid'=>$_W['openid'],'nickname'=>$nickname,'uniacid'=>$_W['uniacid'],'num'=>$num,'type'=>$_GPC['type'],'status'=>'0'));
			
			if($_GPC['type']==1){
			 pdo_update('ewei_shop_member',array('profit_total'=>0,'profit_suc'=>$_GPC['num']),array('openid'=>$_W['openid'],'uniacid'=>$_W['uniacid']));

			} 
			else if($_GPC['type']==2){

			pdo_update('ewei_shop_member',array('com2_total'=>0,'com2_suc'=>$_GPC['num']),array('openid'=>$_W['openid'],'uniacid'=>$_W['uniacid']));
			}
			else if($_GPC['type']==3){

			pdo_update('ewei_shop_member',array('award'=>0,'award_suc'=>$_GPC['num']),array('openid'=>$_W['openid'],'uniacid'=>$_W['uniacid']));
			}
			$credit2=pdo_fetchcolumn('select credit2 from ims_ewei_shop_member where openid=:openid and uniacid=:uniacid',array(':openid'=>$_W['openid'],':uniacid'=>$_W['uniacid']));
			pdo_update('ewei_shop_member',array('credit2'=>$num+$credit2),array('openid'=>$_W['openid'],'uniacid'=>$_W['uniacid']));
			return 'success' ;
		}
		$threshold=pdo_fetchcolumn('select threshold from ims_ewei_shop_sysset where uniacid=:uni',array(':uni'=>$_W['uniacid']));
		$data=pdo_fetch('select * from '.tablename('ewei_shop_member').' where openid=:openid and uniacid=:uni limit 1 ',array(':openid'=>$_W['openid'],':uni'=>$_W['uniacid']));
		if($data['agentid']==0){
			$data['recommender']='总店';
		}
		else{
			$data['recommender']=pdo_fetchcolumn('select nickname from '.tablename('ewei_shop_member').' where id=:agentid and uniacid=:uni limit 1 ',array(':agentid'=>$data['agentid'],':uni'=>$_W['uniacid']));
		}
		
		$data['com_total']=number_format($data['com_total'],2);
		$data['profit_total']=number_format($data['profit_total'],2);
		$data['profit_suc']=number_format($data['profit_suc'],2);
		$data['com2_total']=number_format($data['com2_total'],2);
		$data['com2_suc']=number_format($data['com2_suc'],2);
		$data['award']=number_format($data['award'],2);
		$data['award_suc']=number_format($data['award_suc'],2);
		//是否为合伙人
		if($data['agentlevel']==0){
			$data['levelname']='普通等级';
		}
		else{
			$level_merch=pdo_fetch('select level_merch,levelname from '.tablename('ewei_shop_commission_level').' where uniacid=:uni and id=:id limit 1',array(':uni'=>$_W['uniacid'],':id'=>$data['agentlevel']));
			$data['levelname']=$level_merch['levelname']; 
		}
		

		//合伙人Id查询名下购买记录
		
		
		if($level_merch['level_merch']==2){


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
				$level=pdo_fetchcolumn('select id from ims_ewei_shop_member_level where level_merch=1');
				pdo_update('ewei_shop_member',array('level_merch'=>1,'agentlevel'=>$agentlevel,'level'=>$level,'isagent'=>1,'status'=>1),array('id'=>$_GPC['id']));
				$agent_num=pdo_fetchcolumn('select agent_num from ims_ewei_shop_member where id=:id',array(':id'=>$_GPC['agentid']));
				pdo_update('ewei_shop_member',array('agent_num'=>$agent_num-1),array('id'=>$_GPC['agentid']));
				$award=pdo_fetchcolumn('select award from ims_ewei_shop_member where id=:id',array(':id'=>$_GPC['agentid']));
				pdo_update('ewei_shop_member',array('award'=>$award+300),array('id'=>$_GPC['agentid']));
				return 'bind success';
			}
			
		}
	}
	
	
	
	
}


?>