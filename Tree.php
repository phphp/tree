<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 使用方式
 *
 * 例如：
$cfg = array(
	'data'			=> $arr,
	'pid_key_name'	=> 'Pid',
	'id_key_name'	=> 'Id',
	'selected_id'	=> 3,
	'delimiter'		=> '》',
	'url'			=> '/category/'
);
$this->load->library('tree', $cfg);
$code = $this->tree->get_breadcrumb();
 *
 */
class Tree
{
	public function __construct($cfg)
	{
		$this->data = $cfg['data'];
		if ( isset($cfg['pid']) ) $this->pid = $cfg['pid'];
		if ( isset($cfg['pid_key_name']) ) $this->pid_key_name = $cfg['pid_key_name'];
		if ( isset($cfg['id_key_name']) ) $this->id_key_name = $cfg['id_key_name'];
		if ( isset($cfg['url']) ) $this->url = $cfg['url'];
		if ( isset($cfg['selected_id']) ) $this->selected_id = $cfg['selected_id'];
		if ( isset($cfg['delimiter']) ) $this->delimiter = $cfg['delimiter'];
		if ( isset($cfg['key_name']) ) $this->key_name = $cfg['key_name'];
	}


	// 必要的参数
	private $data;						// 需要处理的数组数据


	// 可选传参
	private $pid 			= 0;		// 需要查询的顶级分类的ID
	private $pid_key_name 	= 'pid';	// 父 ID 键值
	private $id_key_name 	= 'id';		// ID 键值
	private $url			= '';		// 链接地址
	private $selected_id	= -1;		// 需要选中的 list item
	private $delimiter		= '/';		// 面包屑的分隔符
	private $key_name		= 'name';	// 名称 键值


	// 不用修改的参数
	private $level 			= 1;		// 表示数据层级
	private $rs 			= array();	// 返回结果


	/**
	 * 获取列表数组
	 * @return arr 列表数组
	 */
	public function get_list()
	{
		foreach ( $this->data as $k=>$v )
		{
			if ( $v[$this->pid_key_name] == $this->pid )
			{
				$v['level'] = $this->level;
				$this->rs[] = $v;
				unset ( $this->data[$k] );

				$this->pid = $v[$this->id_key_name];
				$this->level++;
				$this->rs = $this->get_list();
				$this->level--;
				$this->pid = $v[$this->pid_key_name];
			}
		}

		return $this->rs;
	}


	/**
	 * 获取 ul 格式的 html 代码
	 * @return str html代码
	 */
	public function get_ul()
	{
		if ( empty($this->rs) ) $this->get_list();

		$rs = '';
		foreach ( $this->rs as $k=>$v )
		{
			if ( $this->selected_id == $v[$this->id_key_name] )
				$rs .= '<li class="tree_li_' . $v['level'] . ' tree_li_selected"><a href="' . $this->url . $v[$this->id_key_name] . '">' . $v[$this->key_name] . '</a></li>';
			else
				$rs .= '<li class="tree_li_' . $v['level'] . '"><a href="' . $this->url . $v[$this->id_key_name] . '">' . $v[$this->key_name] . '</a></li>';
		}

		return '<ul class="tree_container">' . $rs . '</ul>';
	}


	/**
	 * 获取面包屑导航
	 * @return str html代码
	 */
	public function get_breadcrumb()
	{
		if ( empty($this->rs) ) $this->get_list();

		$rs = array();
		foreach ( $this->rs as $v )
		{
			if ( $v[$this->id_key_name] == $this->selected_id )
			{
				$rs[0] = $v;
				break;
			}
		}
		if ( empty($rs) ) die('Can not find $selected_id');

		$tmp = $rs[0][$this->pid_key_name];
		while ( $tmp != $this->pid )
		{
			foreach ( $this->rs as $v )
			{
				if ( $v[$this->id_key_name] == $tmp )
				{
					$rs[] = $v;
					$tmp = $v[$this->pid_key_name];
					break;
				}
			}
		}
		$rs = array_reverse($rs);

		$str = '';
		foreach ( $rs as $v )
		{
			if ( $v[$this->id_key_name] == $this->selected_id )
				$str .= '<a class="breadcrumb_selected" href="' . $this->url . $v[$this->id_key_name] . '">' . $v[$this->key_name] . '</a>';
			else
				$str .= '<a href="' . $this->url . $v[$this->id_key_name] . '">' . $v[$this->key_name] . '</a><span class="delimiter">' . $this->delimiter . '</span>';
		}

		return '<div class="breadcrumb_container">' . $str . '</div>';
	}


	/**
	 * 获取 select 标签的 option
	 * @return str option html 代码
	 */
	public function get_select()
	{
		if ( empty($this->rs) ) $this->get_list();

		$rs = '';
		foreach ( $this->rs as $k=>$v )
		{
			$margin = 15 * ($v['level']-1);
			if ( $this->selected_id == $v[$this->id_key_name] )
				$rs .= '<option style="margin-left: ' . $margin . 'px;" value="' . $v[$this->id_key_name] . '" selected="selected">' . $v[$this->key_name] . '</option>';
			else
				$rs .= '<option style="margin-left: ' . $margin . 'px;" value="' . $v[$this->id_key_name] . '">' . $v[$this->key_name] . '</option>';
		}

		return $rs;
	}

}
