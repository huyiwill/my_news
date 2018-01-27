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
            'pos' => trim($data['pos']),
            'ip'  => $data['ip']
        );
        $r = M('map') -> where($where) -> select();
        if(count($r) == 1){
            $data = array(
                'time'        => date('Y-m-d H:i:s', time()),
            );
            M('map') -> where($where) -> save($data);
            return true;
        }
        $res = M('map')->add($data);
        return $res;
    }
}