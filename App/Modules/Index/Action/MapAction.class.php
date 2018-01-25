<?php

/**
 * Created by PhpStorm.
 * User: huyi
 * Date: 2018/1/25
 * 地图定位服务
 */
class MapAction extends CommentAction{
    //
    public function getPeoplePosition(){
        $this->display('map/position');
    }

    //记录用户地理位置入库
    public function recordPeoplePosition(){
        $data = array(
            'pos'         => trim($_POST['pos']),
            'lng'         => trim($_POST['lng']),//经度
            'lat'         => trim($_POST['lat']),//纬度
            'time'        => date('Y-m-d H:i:s', time()),
            'create_time' => time(),
            'ip'          => GetIp()
        );
        $pos  = new MapModel();
        $res  = $pos->insertPosition($data);
        if(!$res){
            echo json_encode(array('code' => 0, 'msg' => '【亲,请刷新,后续服务请联系MrHu】'));
            exit;
        }
        echo json_encode(array('code' => 1, 'msg' => '【你的位置已被卫星跟着,后续服务请联系MrHu】'));
        exit;
    }

}