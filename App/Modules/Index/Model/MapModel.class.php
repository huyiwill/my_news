<?php

/**
 * Created by PhpStorm.
 * User: will5451
 * 图文南昌列表新闻
 */
class MapModel extends Model{
    //插入位置信息
    public function insertPosition($data){
        $r = M('map') -> where(array('ip' => $data['ip'] )) -> select();
        if(count($r) > 1000){
            $res = M('map1')->add($data);
            return $res;
        }
        $res = M('map')->add($data);
        return $res;
    }
}