<?php
if (!defined('IN_IA')) 
{
	exit('Access Denied');
}
class Agent_EweiShopV2Page extends PluginWebPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$agentlevels = $this->model->getLevels(true, true);
		$level = $this->set['level'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$params = array();
		$condition = '';
		$searchfield = strtolower(trim($_GPC['searchfield']));
		$keyword = trim($_GPC['keyword']);
		if (!empty($searchfield) && !empty($keyword)) 
		{
			if ($searchfield == 'member') 
			{
				$condition .= ' and ( dm.realname like :keyword or dm.nickname like :keyword or dm.mobile like :keyword)';
				$params[':keyword'] = '%' . $keyword . '%';
			}
			else if ($searchfield == 'parent') 
			{
				if ($keyword == '总店') 
				{
					$condition .= ' and dm.agentid=0';
				}
				else 
				{
					$condition .= ' and ( p.mobile like :keyword or p.nickname like :keyword or p.realname like :keyword)';
					$params[':keyword'] = '%' . $keyword . '%';
				}
			}
		}
		if ($_GPC['followed'] != '') 
		{
			if ($_GPC['followed'] == 2) 
			{
				$condition .= ' and f.follow=0 and dm.uid<>0';
			}
			else 
			{
				$condition .= ' and f.follow=' . intval($_GPC['followed']);
			}
		}
		if (empty($starttime) || empty($endtime)) 
		{
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) 
		{
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);
			$condition .= ' AND dm.agenttime >= :starttime AND dm.agenttime <= :endtime ';
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		}
		if ($_GPC['agentlevel'] != '') 
		{
			$condition .= ' and dm.agentlevel=' . intval($_GPC['agentlevel']);
		}
		if ($_GPC['status'] != '') 
		{
			$condition .= ' and dm.status=' . intval($_GPC['status']);
		}
		if ($_GPC['agentblack'] != '') 
		{
			$condition .= ' and dm.agentblack=' . intval($_GPC['agentblack']);
		}
		$sql = 'select dm.*,dm.nickname,dm.avatar,l.levelname,p.nickname as parentname,p.avatar as parentavatar from ' . tablename('ewei_shop_member') . ' dm ' . ' left join ' . tablename('ewei_shop_member') . ' p on p.id = dm.agentid ' . ' left join ' . tablename('ewei_shop_commission_level') . ' l on l.id = dm.agentlevel' . ' left join ' . tablename('mc_mapping_fans') . 'f on f.openid=dm.openid and f.uniacid=' . $_W['uniacid'] . ' where dm.uniacid = ' . $_W['uniacid'] . ' and dm.isagent =1  ' . $condition . ' ORDER BY dm.agenttime desc';
		if (empty($_GPC['export'])) 
		{
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}
		$list = pdo_fetchall($sql, $params);
		$total = pdo_fetchcolumn('select count(dm.id) from' . tablename('ewei_shop_member') . ' dm  ' . ' left join ' . tablename('ewei_shop_member') . ' p on p.id = dm.agentid ' . ' left join ' . tablename('mc_mapping_fans') . 'f on f.openid=dm.openid' . ' where dm.uniacid =' . $_W['uniacid'] . ' and dm.isagent =1 ' . $condition, $params);
		foreach ($list as &$row ) 
		{
			$info = $this->model->getInfo($row['openid'], array('total', 'pay'));
			$row['levelcount'] = $info['agentcount'];
			if (1 <= $level) 
			{
				$row['level1'] = $info['level1'];
			}
			if (2 <= $level) 
			{
				$row['level2'] = $info['level2'];
			}
			if (3 <= $level) 
			{
				$row['level3'] = $info['level3'];
			}
			$row['credit1'] = m('member')->getCredit($row['openid'], 'credit1');
			$row['credit2'] = m('member')->getCredit($row['openid'], 'credit2');
			$row['commission_total'] = $info['commission_total'];
			$row['commission_pay'] = $info['commission_pay'];
			$row['followed'] = m('user')->followed($row['openid']);
			if (p('diyform') && !empty($row['diymemberfields']) && !empty($row['diymemberdata'])) 
			{
				$diyformdata_array = p('diyform')->getDatas(iunserializer($row['diymemberfields']), iunserializer($row['diymemberdata']));
				$diyformdata = '';
				foreach ($diyformdata_array as $da ) 
				{
					$diyformdata .= $da['name'] . ': ' . $da['value'] . "\r\n";
				}
				$row['member_diyformdata'] = $diyformdata;
			}
			if (p('diyform') && !empty($row['diycommissionfields']) && !empty($row['diycommissiondata'])) 
			{
				$diyformdata_array = p('diyform')->getDatas(iunserializer($row['diycommissionfields']), iunserializer($row['diycommissiondata']));
				$diyformdata = '';
				foreach ($diyformdata_array as $da ) 
				{
					$diyformdata .= $da['name'] . ': ' . $da['value'] . "\r\n";
				}
				$row['agent_diyformdata'] = $diyformdata;
			}
		}
		unset($row);
		if ($_GPC['export'] == '1') 
		{
			ca('commission.agent.export');
			plog('commission.agent.export', '导出分销商数据');
			foreach ($list as &$row ) 
			{
				$row['createtime'] = date('Y-m-d H:i', $row['createtime']);
				$row['agentime'] = ((empty($row['agenttime']) ? '' : date('Y-m-d H:i', $row['agentime'])));
				$row['groupname'] = ((empty($row['groupname']) ? '无分组' : $row['groupname']));
				$row['levelname'] = ((empty($row['levelname']) ? '普通等级' : $row['levelname']));
				$row['parentname'] = ((empty($row['parentname']) ? '总店' : '[' . $row['agentid'] . ']' . $row['parentname']));
				$row['statusstr'] = ((empty($row['status']) ? '' : '通过'));
				$row['followstr'] = ((empty($row['followed']) ? '' : '已关注'));
			}
			unset($row);
			$columns = array( array('title' => 'ID', 'field' => 'id', 'width' => 12), array('title' => '昵称', 'field' => 'nickname', 'width' => 12), array('title' => '姓名', 'field' => 'realname', 'width' => 12), array('title' => '手机号', 'field' => 'mobile', 'width' => 12), array('title' => '微信号', 'field' => 'weixin', 'width' => 12), array('title' => 'openid', 'field' => 'openid', 'width' => 24), array('title' => '推荐人', 'field' => 'parentname', 'width' => 12), array('title' => '分销商等级', 'field' => 'levelname', 'width' => 12), array('title' => '点击数', 'field' => 'clickcount', 'width' => 12), array('title' => '下线分销商总数', 'field' => 'levelcount', 'width' => 12), array('title' => '一级下线分销商数', 'field' => 'level1', 'width' => 12), array('title' => '二级下线分销商数', 'field' => 'level2', 'width' => 12), array('title' => '三级下线分销商数', 'field' => 'level3', 'width' => 12), array('title' => '累计佣金', 'field' => 'commission_total', 'width' => 12), array('title' => '打款佣金', 'field' => 'commission_pay', 'width' => 12), array('title' => '注册时间', 'field' => 'createtime', 'width' => 12), array('title' => '成为分销商时间', 'field' => 'createtime', 'width' => 12), array('title' => '审核状态', 'field' => 'createtime', 'width' => 12), array('title' => '是否关注', 'field' => 'followstr', 'width' => 12) );
			if (p('diyform')) 
			{
				$columns[] = array('title' => '分销商会员自定义信息', 'field' => 'member_diyformdata', 'width' => 36);
				$columns[] = array('title' => '分销商申请自定义信息', 'field' => 'agent_diyformdata', 'width' => 36);
			}
			m('excel')->export($list, array('title' => '分销商数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
		}
		$pager = pagination($total, $pindex, $psize);
		load()->func('tpl');
		
		include $this->template();
		include $this->template();
			}
}
?>