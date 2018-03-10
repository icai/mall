<?php
if (!defined('IN_IA')) 
{
	exit('Access Denied');
}
class Change_EweiShopV2Page extends PluginWebPage 
{
	public function main() 
	{

		global $_GPC;
		global $_W;
		
		//  $_GPC['openid']='oYnP20ZPKqZcamozRDr9DmwH8U8o';
		// $_GPC['uniacid']=6;
		// $this->Enfunds(1,$_GPC['openid']);exit();
		
		
		if($_W['ispost']){
			$result=pdo_update('ewei_shop_sysset',array('threshold'=>$_GPC['threshold']),array('uniacid'=>$_W['uniacid'])); 
			if($result){
				message('设定成功',$this->createWebUrl('web',array('r'=>'commission.change','status'=>1)),'success');
			}
			
			}
		
		$threshold=pdo_fetchcolumn('select threshold from ims_ewei_shop_sysset where uniacid=:u',array(":u"=>$_W['uniacid']));

		include $this->template();
	}
	
}