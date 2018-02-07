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
		// $_GPC['openid']='oYnP20ZPKqZcamozRDr9DmwH8U8o';
		// $_GPC['uniacid']=6;
		if($_GPC['action']=='pass'){

			//$this->Enfunds($_GPC['num'],$_GPC['openid']);
			pdo_update('ewei_shop_withdraw',array('status'=>1),array('id'=>$_GPC['id']));
			if($_GPC['type']==1){
	
				$res=pdo_fetch('select profit_withdraw,profit_suc from ims_ewei_shop_member where openid=:openid and uniacid=:uniacid',array(':openid'=>$_GPC['openid'],':uniacid'=>$_GPC['uniacid']));
				
				pdo_update('ewei_shop_member',array('profit_withdraw'=>$res['profit_withdraw']-$_GPC['num'],'profit_suc'=>$res['profit_suc']+$_GPC['num']),array('openid'=>$_GPC['openid'],'uniacid'=>$_GPC['uniacid']));
			}
			elseif ($_GPC['type']==2) {
				$res=pdo_fetch('select com2_withdraw,com2_suc from ims_ewei_shop_member where openid=:openid and uniacid=:uniacid',array(':openid'=>$_GPC['openid'],':uniacid'=>$_GPC['uniacid']));
				pdo_update('ewei_shop_member',array('com2_withdraw'=>$res['com2_withdraw']-$_GPC['num'],'com2_suc'=>$res['com2_suc']+$_GPC['num']),array('openid'=>$_GPC['openid'],'uniacid'=>$_GPC['uniacid']));
			}
			
			

			
		}
		elseif($_GPC['action']=='refuse'){
			pdo_update('ewei_shop_withdraw',array('status'=>2),array('id'=>$_GPC['id']));
				if($_GPC['type']==1){
				$res=pdo_fetch('select profit_withdraw,profit_total from ims_ewei_shop_member where openid=:openid and uniacid=:uniacid',array(':openid'=>$_GPC['openid'],':uniacid'=>$_GPC['uniacid']));
				
				pdo_update('ewei_shop_member',array('profit_withdraw'=>$res['profit_withdraw']-$_GPC['num'],'profit_total'=>$res['profit_total']+$_GPC['num']),array('openid'=>$_GPC['openid'],'uniacid'=>$_GPC['uniacid']));
			}
			elseif ($_GPC['type']==2) {
				$res=pdo_fetch('select com2_withdraw,com2_total from ims_ewei_shop_member where openid=:openid and uniacid=:uniacid',array(':openid'=>$_GPC['openid'],':uniacid'=>$_GPC['uniacid']));
				pdo_update('ewei_shop_member',array('com2_withdraw'=>$res['com2_withdraw']-$_GPC['num'],'com2_total'=>$res['com2_total']+$_GPC['num']),array('openid'=>$_GPC['openid'],'uniacid'=>$_GPC['uniacid']));
			}
		}	
		


		if($_GPC['status']==1){
			$data=pdo_fetchall('select * from ims_ewei_shop_withdraw where status=:status',array(':status'=>0));
			$msg['header']='待审核申请';
		}
		elseif($_GPC['status']==2){
			$data=pdo_fetchall('select * from ims_ewei_shop_withdraw where status!=:status',array(':status'=>0));
			$msg['header']='已审核申请';
		}


		
		
		include $this->template();
	}
	private function Enfunds($amount,$openid){
			global $_W,$_GPC;
			$setting = uni_setting_load('payment', $_W['uniacid']);
			$pay_setting = $setting['payment'];
		
			$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
			$this_data='timeA'.time().'A';
			load()->func('communication');
			$pars = array();
			
			$api = $pay_setting['wechat'];
			$pars = array(
				'mch_appid' =>$_W['account']['key'],
				'mchid' => $api['mchid'],
				'nonce_str'=>random(32),
				'partner_trade_no'=>$this_data.random(10, 1),
				'openid'=>$openid,
				'check_name'=>'NO_CHECK',
				'amount'=>$amount,
				'desc'=>'您所提现的收益额',
				'spbill_create_ip'=>$_SERVER['SERVER_ADDR']
			);
			ksort($pars, SORT_STRING);
			$string1 = "";
			foreach($pars as $k => $v) {
				$string1 .= "{$k}={$v}&";
			}
			$string1 .= "key={$api['password']}";
			$pars['sign'] = strtoupper(md5($string1));
			$xml = array2xml($pars);
			$myfile = fopen("../addons/ly_film_food/newfile.xml", "w") or die("Unable to open file!");
			$extrasa = array();
			$extrasa['CURLOPT_CAINFO'] ='../addons/ly_film_food/cert/rootca.pem.' . '2';
			$extrasa['CURLOPT_SSLCERT'] ='../addons/ly_film_food/cert/apiclient_cert.pem.' . '2';
			$extrasa['CURLOPT_SSLKEY'] ='../addons/ly_film_food/cert/apiclient_key.pem.' . '2';
			$resp = ihttp_request($url,$xml,$extrasa);
			if(is_error($resp)){
				message("网络连接错误");
			}else{
				$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
				$dom = new DOMDocument();
				if($dom->loadXML($xml)){
					$xpath = new DOMXPath($dom);
					$return_code = $xpath->evaluate('string(//xml/return_code)');
					$result_code = $xpath->evaluate('string(//xml/result_code)');
					$return_all=array();
					if(strtolower($return_code) == 'success' && strtolower($result_code) == 'success'){
						$return_all['isok'] = true;
						$return_all['mch_appid'] = $xpath->evaluate('string(//xml/mch_appid)');
						$return_all['mchid'] = $xpath->evaluate('string(//xml/mchid)');
						$return_all['partner_trade_no'] = $xpath->evaluate('string(//xml/partner_trade_no)');
						$return_all['payment_no']=$xpath->evaluate('string(//xml/payment_no)');
						$return_all['payment_time']=$xpath->evaluate('string(//xml/payment_time)');
					}else{
						$return_all['isok'] = false;
						$return_all['return_msg']=$xpath->evaluate('string(//xml/return_msg)');
						$return_all['err_code_des']=$xpath->evaluate('string(//xml/err_code_des)');
					}
					$return_all['return_code']=$return_code;
					$return_all['result_code']=$result_code;

						fwrite($myfile, $resp['content']);
			fclose($myfile);
					return  $return_all;
				}
				// $procResult = true;
			}

		
		}
}