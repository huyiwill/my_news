<?php

/**
 * Created by PhpStorm.
 * User: will5451
 * 图文南昌列表新闻
 */
class MapModel extends Model{
    //插入位置信息
    public function insertPosition($data){
        $lng = $data['lng'];
        $lat = $data['lat'];
        $where = array(
            'lng' => $lng,
            'lat' => $lat,
            'ip'  => $data['ip']
        );
        $r = M('map') -> where($where) -> select();
        if(count($r) > 50){
            //$res = M('map1')->add($data);
            return true;
        }
        $res = M('map')->add($data);
        return $res;
    }
}