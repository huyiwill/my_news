<?php

/**
 * Menu(菜单管理)
 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class MenuController extends AdminbaseController {

	protected $Menu;

	function _initialize() {
		parent::_initialize();
		$this->Menu = D("Menu");
	}

	/**
	 *  显示菜单
	 */
	public function index() {
		$result = $this->Menu->order(array("listorder" => "ASC"))->select();
		$tree = new \Common\Lib\Util\Tree();
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		foreach ($result as $r) {
			$r['str_manage'] = '<a href="' . U("Menu/add", array("parentid" => $r['id'], "menuid" => $_GET['menuid'])) . '">添加子菜单</a> | <a href="' . U("Menu/edit", array("id" => $r['id'], "menuid" => $_GET['menuid'])) . '">修改</a> | <a class="J_ajax_del" href="' . U("Menu/delete", array("id" => $r['id'], "menuid" => I("get.menuid"))) . '">删除</a> ';
			$r['status'] = $r['status'] ? "显示" : "不显示";
			$array[] = $r;
		}

		$tree->init($array);
		$str = "<tr>
	<td align='center'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input'></td>
	<td align='center'>\$id</td>
	<td >\$spacer\$name</td>
                    <td align='center'>\$status</td>
	<td align='center'>\$str_manage</td>
	</tr>";
		$categorys = $tree->get_tree(0, $str);
		$this->assign("categorys", $categorys);
		$this->display();
	}

	/**
	 *  添加
	 */
	public function add() {
		if (IS_POST) {
			if ($this->Menu->create()) {
				if ($this->Menu->add()) {
					$this->success("新增成功！", U("Menu/index"));
				} else {
					$this->error("新增失败！");
				}
			} else {
				$this->error($this->Menu->getError());
			}
		} else {
			$tree = new \Common\Lib\Util\Tree();
			$parentid = (int) I("get.parentid");
			$result = $this->Menu->select();
			foreach ($result as $r) {
				$r['selected'] = $r['id'] == $parentid ? 'selected' : '';
				$array[] = $r;
			}
			$str = "<option value='\$id' \$selected>\$spacer \$name</option>";
			$tree->init($array);
			$select_categorys = $tree->get_tree(0, $str);
			$this->assign("select_categorys", $select_categorys);
			$this->display();
		}
	}

	/**
	 *  删除
	 */
	public function delete() {
		$id = (int) I("get.id");
		$count = $this->Menu->where(array("parentid" => $id))->count();
		if ($count > 0) {
			$this->error("该菜单下还有子菜单，无法删除！");
		}
		if ($this->Menu->delete($id)) {
			$this->success("删除菜单成功！");
		} else {
			$this->error("删除失败！");
		}
	}

	/**
	 *  编辑
	 */
	public function edit() {
		if (IS_POST) {
			if ($this->Menu->create()) {
				if ($this->Menu->save() !== false) {
					$this->success("更新成功！", U("Menu/index"));
				} else {
					$this->error("更新失败！");
				}
			} else {
				$this->error($this->Menu->getError());
			}
		} else {
			$tree = new \Common\Lib\Util\Tree();
			$id = (int) I("get.id");
			$rs = $this->Menu->where(array("id" => $id))->find();
			$result = $this->Menu->select();
			foreach ($result as $r) {
				$r['selected'] = $r['id'] == $rs['parentid'] ? 'selected' : '';
				$array[] = $r;
			}
			$str = "<option value='\$id' \$selected>\$spacer \$name</option>";
			$tree->init($array);
			$select_categorys = $tree->get_tree(0, $str);
			$this->assign("data", $rs);
			$this->assign("select_categorys", $select_categorys);
			$this->display();
		}
	}

	//排序
	public function listorders() {
		$status = parent::listorders($this->Menu);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}

	//常用菜单
	public function public_changyong() {
		if (IS_POST) {
			$menu = I("post.menu");
			if (count($menu) > 15) {
				$this->error("常用菜单设置不宜超过15个！");
			}
			//先删除旧的
			M("AdminPanel")->where(array("userid" => AppframeAction::$Cache['uid']))->delete();
			foreach ($menu as $k => $menuid) {
				$info = M("Menu")->where(array("id" => $menuid))->find();
				if ($info) {
					$app = $info['app'];
					$model = $info['model'];
					$action = $info['action'];
					$url = U("$app/$model/$action", $info['data'] . "&menuid=$menuid");
					M("AdminPanel")->add(array(
						"menuid"	 => $menuid,
						"userid"	 => AppframeAction::$Cache['uid'],
						"name"		 => $info['name'],
						"url"		 => $url,
						"datetime"	 => time(),
					));
				}
			}
			$this->success("添加成功！");
		} else {
			$data = M("Menu")->where(array("type" => 1, "status" => 1))->select();
			$Panel_data = M("AdminPanel")->where(array("userid" => AppframeAction::$Cache['uid']))->field("menuid")->select();
			foreach ($Panel_data as $r) {
				$Panel[] = $r['menuid'];
			}
			$dataArr = array();
			$menuName = array();
			$Module = F("Module");
			foreach ($data as $k => $v) {
				$dataArr[ucwords($v['app'])][] = $v;
				$menuName[ucwords($v['app'])] = $Module[ucwords($v['app'])]['name'];
			}
			$this->assign("data", $dataArr);
			$this->assign("panel", $Panel);
			$this->assign("name", $menuName);
			$this->display();
		}
	}

	public function export_menu() {
		$menu_obj = D("Menu");
		$rootmenus = $menu_obj->menu(0);
		$apps = \Common\Lib\Util\Dir::getList(APP_PATH);
		foreach ($apps as $a) {
			if (is_dir(APP_PATH . $a)) {
				if (!(strpos($a, ".") === 0)) {
					$menudir = APP_PATH . $a . DIRECTORY_SEPARATOR . "Menu";
					\Common\Lib\Util\Dir::del($menudir);
				}
			}
		}
		foreach ($rootmenus as $m) {
			$rootmenu = $m;
			$this->generate_submenu($rootmenu, $m);
			$app = ucfirst($m['app']);
			$menudir = APP_PATH . $app . "/Menu";
			$model = ucfirst($m['model']);
			$action = ucfirst($m['action']);
			$menuname = "$model.$action.php";
			file_put_contents($menudir . "/" . $menuname, "<?php\treturn " . var_export($rootmenu, true) . ";?>");
		}
		$this->display();
	}

	public function dev_import_menu() {
		$menus = F("Menu");
		if (!empty($menus)) {
			$table_menu = C('DB_PREFIX') . "menu";
			$this->Menu->execute("TRUNCATE TABLE $table_menu;");
			foreach ($menus as $menu) {
				$this->Menu->add($menu);
			}
		}

		$this->display();
	}

	public function import_menu() {
		$apps = Dir::getList(SPAPP);
		foreach ($apps as $a) {
			if (is_dir(SPAPP . $a)) {
				if (!(strpos($a, ".") === 0)) {
					$menudir = SPAPP . $a . "/Menu";
					$menus = scandir($menudir);
					if (count($menus)) {
						foreach ($menus as $mf) {
							$rootmenudatas = include $menudir . "/$mf";

							print_r($rootmenudatas);
							if (!empty($rootmenudatas)) {
								$data = $rootmenudatas;
								$data['parentid'] = 0;
								unset($data['items']);
								print_r($data);
								$id = $this->Menu->add($data);
								if (!empty($rootmenudatas['items'])) {
									$this->import_submenu($rootmenudatas['items'], $id);
								}
							}
						}
					}
				}
			}
		}
	}

	private function import_submenu($submenus, $parentid) {
		foreach ($submenus as $sm) {
			$data = $sm;
			$data['parentid'] = $parentid;
			unset($data['items']);
			$id = $this->Menu->add($data);
			if (!empty($sm['items'])) {
				$this->import_submenu($sm['items'], $id);
			} else {
				return;
			}
		}
	}

	private function generate_submenu(&$rootmenu, $m) {
		$parentid = $m['id'];
		$rm = $this->Menu->menu($parentid);
		unset($rootmenu['id']);
		unset($rootmenu['parentid']);
		if (count($rm)) {

			$count = count($rm);
			for ($i = 0; $i < $count; $i++) {
				$this->generate_submenu($rm[$i], $rm[$i]);
			}
			$rootmenu['items'] = $rm;
		} else {
			return;
		}
	}

}
