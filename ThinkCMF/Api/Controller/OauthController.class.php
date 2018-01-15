<?php

namespace Api\Controller;

use Common\Controller\AdminbaseController;

class OauthController extends AdminbaseController {

	function _initialize() {
		
	}

	//登录地址
	public function login($type = null) {
		empty($type) && $this->error('参数错误');

		//加载ThinkOauth类并实例化一个对象
		$sns = \Common\Lib\Api\ThinkOauth::getInstance($type);

		//die(OAUTH_URL_CALLBACK);
		//跳转到授权页面
		redirect($sns->getRequestCodeURL());
	}

	//授权回调地址
	public function callback($type = null, $code = null) {
		header('content-type:text/html;charset=UTF-8;');
		(empty($type) || empty($code)) && $this->error('参数错误');

		//加载ThinkOauth类并实例化一个对象

		$sns = \Common\Lib\Api\ThinkOauth::getInstance($type);

		//腾讯微博需传递的额外参数
		$extend = null;
		if ($type == 'tencent') {
			$extend = array('openid' => I('get.openid'), 'openkey' => I('get.openkey'));
		}

		//请妥善保管这里获取到的Token信息，方便以后API调用
		//调用方法，实例化SDK对象的时候直接作为构造函数的第二个参数传入
		//如： $qq = ThinkOauth::getInstance('qq', $token);
		$token = $sns->getAccessToken($code, $extend);
		//die($token);
		//获取当前登录用户信息
		if (is_array($token)) {
			$user_info = A('Type', 'Event')->$type($token);
			$OMember = M('OauthMember');
			//账户是否已经存在
			$rst = $OMember->where("_from='{$type}' and openid='{$token['openid']}' and status=1")->find();
			if (isset($_SESSION["MEMBER_id"])) {
				//如果用户已经local账户登陆过，则表示要绑定第三方
				if (!empty($rst)) {
					$this->error('该帐号已被本站其他账号绑定！', U("portal/center/index"));
					exit();
				}
				$data = array(
					'_from'				 => $type,
					'_name'				 => $user_info['name'],
					'head_img'			 => $user_info['head'],
					'create_time'		 => time(),
					'lock_to_id'		 => $_SESSION["MEMBER_id"],
					'last_login_time'	 => time(),
					'last_login_ip'		 => get_client_ip(),
					'login_times'		 => 1,
					'status'			 => 1,
					'access_token'		 => $token['access_token'],
					'expires_date'		 => (int) (time() + $token['expires_in']),
					'openid'			 => $token['openid'],
				);
				if ($OMember->add($data)) {
					$this->success('账号绑定成功！', U("portal/center/index"));
				} else {
					$this->error('账号绑定失败！', U("portal/center/index"));
				}
			} else if ($rst) {
				//数据库已经有该用户登录信息
				$data = array(
					'last_login_time'	 => time(),
					'last_login_ip'		 => get_client_ip(),
					'login_times'		 => $rst['login_times'] + 1,
					'access_token'		 => $token['access_token'],
					'expires_date'		 => (int) (time() + $token['expires_in']),
				);
				$OMember->where("_from='{$type}' and openid='{$token['openid']}'")->save($data);
				$_SESSION["MEMBER_type"] = $type;
				$_SESSION["MEMBER_id"] = $rst['lock_to_id'];
				$_SESSION['MEMBER_name'] = $rst["_name"];
				$this->success('登录成功！', U("portal/center/index"));
			} else if ($OMember->where("_from='{$type}' and openid='{$token['openid']}' and status=0")->find()) {
				$this->error('您可能已经被列入黑名单，请联系网站管理员！', U("portal/index/index"));
			} else {
				//本地用户中创建对应一条数据
				$mem_insert = array(
					'user_login_name'	 => $type . '游客',
					'user_pic_assetid'	 => $user_info['head'],
					'last_login_time'	 => time(),
					'last_login_ip'		 => get_client_ip(),
					'create_time'		 => time(),
					'user_status'		 => '1',
				);
				$id = M("Members")->add($mem_insert);
				//第三方用户表中创建数据
				$data = array(
					'_from'				 => $type,
					'_name'				 => $user_info['name'],
					'head_img'			 => $user_info['head'],
					'create_time'		 => time(),
					'lock_to_id'		 => $id,
					'last_login_time'	 => time(),
					'last_login_ip'		 => get_client_ip(),
					'login_times'		 => 1,
					'status'			 => 1,
					'access_token'		 => $token['access_token'],
					'expires_date'		 => (int) (time() + $token['expires_in']),
					'openid'			 => $token['openid'],
				);
				if ($OMember->add($data)) {
					$_SESSION["MEMBER_type"] = $type;
					$_SESSION["MEMBER_id"] = $id;
					$_SESSION['MEMBER_name'] = $user_info['name'];
					$this->success('登录成功！', U("portal/center/index"));
				} else {
					$this->error('用户信息获取失败！', U("portal/index/index"));
				}
			}
		} else {
			$this->success('登录失败！', U("portal/index/index"));
		}
	}

}
