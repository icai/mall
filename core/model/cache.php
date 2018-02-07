<?php
if (!defined('IN_IA')) 
{
	exit('Access Denied');
}
class Cache_EweiShopV2Model 
{
	public function get_key($key = '', $uniacid = '') 
	{
		global $_W;
		if (empty($uniacid)) 
		{
			$uniacid = $_W['uniacid'];
		}
		return hunter_mall_PREFIX . md5($uniacid . '_new_' . $key);
	}
	public function getArray($key = '', $uniacid = '') 
	{
		return $this->get($key, $uniacid);
	}
	public function getString($key = '', $uniacid = '') 
	{
		return $this->get($key, $uniacid);
	}
	//从文件中获取key对应的内容
	public function get($key = '', $uniacid = '') 
	{
		return cache_read($this->get_key($key, $uniacid));
	}
	public function set($key = '', $value = NULL, $uniacid = '') 
	{
		cache_write($this->get_key($key, $uniacid), $value);
	}
}
?>